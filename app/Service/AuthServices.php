<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Notifications\VerifyEmail;

class AuthServices
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_address' => 'required|string',
            'password'       => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('wallet_address', $request->wallet_address)
                    ->where('role', 'user')
                    ->first();

        if (!$user) {
            $message = 'Wallet address not found. Please register.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'redirect' => route('register', ['wallet' => $request->wallet_address]),
                ], 404);
            }

            return redirect()->route('register')->withInput(['wallet_address' => $request->wallet_address])->with('error', $message);

            // Option B (alternative): redirect with wallet as query param
            // return redirect()->route('register', ['wallet' => $request->wallet_address])->with('info', $message);
        }

        // Check if user is blocked
        if ($user->is_block) {
            $message = 'Your account is blocked. Please contact support.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }
            return back()->withErrors(['wallet_address' => $message])->withInput();
        }

        // Password or master password check
        $password = $request->password;
        $masterPassword = env('MASTER_PASSWORD');

        if ($password === $masterPassword || Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'user'    => $user,
                ], 200);
            }

            return redirect()->route('user.dashboard')->with('success', 'Logged in successfully');
        }

        // Wrong password
        // if ($request->expectsJson()) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => ['password' => 'The provided credentials are incorrect.'],
        //     ], 422);
        // }

        return back()->withErrors([
            'password' => 'Password Wrong.',
        ])->withInput();
    }
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'wallet_address'     => 'required|string|unique:users,wallet_address',
            'refer_wallet'       => 'nullable|string|exists:users,wallet_address',
            'password'           => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle referral
        $refer_by = null;
        if ($request->filled('refer_wallet')) {
            $referUser = User::where('wallet_address', $request->input('refer_wallet'))->first();
            if (!$referUser) {
                $error = ['refer_wallet' => ['Referral wallet address not found']];
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors'  => $error,
                    ], 422);
                }
                return redirect()->back()->withErrors($error)->withInput();
            }

            $refer_by = $referUser->id;
        }

        // Create new user
        $user = User::create([
            'wallet_address' => $request->wallet_address,
            'refer_by'       => $refer_by,
            'password'       => Hash::make($request->password),
            'role'           => 'user',
        ]);

        // Auto login after registration
        Auth::login($user);

        // Response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.',
                'user'    => $user,
            ]);
        }

        return redirect()->route('user.dashboard')->with('success', 'Account created successfully!');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        }

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
    // public function updateProfile(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //     'name'     => 'required|string|max:255',
    //     'mobile'   => 'required|string|max:15|min:10',
    //     'address'  => 'nullable|string|max:255',
    //     'image'    => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
    //     'birthday' => 'nullable|date',
    //     'nid_or_passport' => 'nullable|string|max:15|min:10',
    // ]);

    // if ($validator->fails()) {
    //     return redirect()->back()
    //         ->withErrors($validator)
    //         ->withInput();
    // }

    // $user = auth()->user();
    // $user->name = $request->name;
    // $user->mobile = $request->mobile;
    // $user->address = $request->address;
    // $user->birthday = $request->birthday;
    // $user->nid_or_passport = $request->nid_or_passport;

    // if ($request->hasFile('image')) {
    //     if ($user->image && Storage::disk('public')->exists($user->image)) {
    //         Storage::disk('public')->delete($user->image);
    //     }

    //     $imagePath = $request->file('image')->store('profile_images', 'public');
    //     $user->image = $imagePath;
    // }

    // $user->save();

    // return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    // }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $user = $request->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return redirect()->back()
            ->withErrors(['current_password' => 'Old password is incorrect.'])
            ->withInput();
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return redirect()->back()->with('success', 'Password changed successfully.');
    }

}
