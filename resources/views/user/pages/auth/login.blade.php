<!DOCTYPE html>
<html lang="en">
<head>
    @php
        use App\Models\GeneralSetting;
        $generalSettings = GeneralSetting::first();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $generalSettings->app_name ?? 'Edulife' }}</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/user/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/user/vendors/css/vendor.bundle.base.css') }}">
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/user/css/style.css') }}">
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
</head>
<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="row w-100 m-0">
            <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">
                <div class="card col-lg-4 mx-auto">
                    <div class="card-body px-5 py-5">
                        <h3 class="card-title text-left mb-3">User Login</h3>

                        {{-- Display Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Display Flash Messages --}}
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('info'))
                            <div class="alert alert-info">
                                {{ session('info') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-group">
                                <label for="wallet_address">Wallet Address *</label>
                                <input type="text" name="wallet_address" id="wallet_address" class="form-control text-white p_input"
                                       value="{{ old('wallet_address') }}" placeholder="Enter your wallet address" required autofocus>
                            </div>

                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" name="password" id="password"
                                       class="form-control text-white p_input" required>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-between">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="remember"
                                               {{ old('remember') ? 'checked' : '' }}>
                                        Remember me
                                    </label>
                                </div>
                                {{-- <a href="{{ route('password.request') }}" class="forgot-pass">Forgot password</a> --}}
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block enter-btn">Login</button>
                            </div>

                            <p class="sign-up mt-3">
                                Don't have an account? <a href="{{ route('register') }}">Sign Up</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- row ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>

<!-- plugins:js -->
<script src="{{ asset('assets/user/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/user/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/user/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('assets/user/js/misc.js') }}"></script>
<script src="{{ asset('assets/user/js/settings.js') }}"></script>
<script src="{{ asset('assets/user/js/todolist.js') }}"></script>
</body>
</html>
