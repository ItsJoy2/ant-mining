<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Investor;
use App\Models\Transactions;
use App\Models\GlobalIncomeSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyBonus extends Command
{
    protected $signature = 'investment:daily-bonus';
    protected $description = 'Distribute daily/monthly returns to users and referral bonuses + collect global pool income';

    public function handle()
    {
        $now = Carbon::now()->toDateString();

        // Get all running investments due for return
        $investments = Investor::where('status', 'running')
            ->whereDate('next_return_date', '<=', $now)
            ->with(['user', 'package'])
            ->get();

        if ($investments->isEmpty()) {
            $this->info("No investments due today.");
            return;
        }

        $this->info("Processing " . $investments->count() . " active investments...");

        foreach ($investments as $investment) {
            DB::beginTransaction();

            try {
                $user = $investment->user;
                $package = $investment->package;

                if (!$user || !$package) {
                    Log::warning("Skipping investment #{$investment->id} — Missing user or package.");
                    DB::rollBack();
                    continue;
                }

                //Calculate ROI for main user
                $dailyReturn = round($investment->expected_return, 8);

                // Add return to user's spot wallet
                $user->increment('spot_wallet', $dailyReturn);

                // Record transaction
                Transactions::create([
                    'transaction_id' => Transactions::generateTransactionId(),
                    'user_id' => $user->id,
                    'amount' => $dailyReturn,
                    'remark' => "daily_pnl",
                    'type' => '+',
                    'status' => 'Paid',
                    'details' => ucfirst($investment->return_type) . " Bonus from investment Plan: {$package->plan_name}",
                    'charge' => 0,
                ]);

                //Add Global Income Pool share
                $globalSettings = GlobalIncomeSetting::first();
                if ($globalSettings && $globalSettings->roi_percentage > 0) {
                    $globalPoolAmount = round($dailyReturn * ($globalSettings->roi_percentage / 100), 8);

                    if ($globalPoolAmount > 0) {
                        DB::table('global_income_pools')->insert([
                            'amount' => $globalPoolAmount,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Referral Bonus
                if (!empty($user->refer_by)) {
                    $referrer = User::find($user->refer_by);

                    if ($referrer) {
                        $referralBonus = round($dailyReturn * ($investment->referral_bonus / 100), 8);

                        if ($referralBonus > 0) {
                            $referrer->increment('spot_wallet', $referralBonus);

                            Transactions::create([
                                'transaction_id' => Transactions::generateTransactionId(),
                                'user_id' => $referrer->id,
                                'amount' => $referralBonus,
                                'remark' => "pnl_bonus",
                                'type' => '+',
                                'status' => 'Paid',
                                'details' => "PNL bonus from referral user {$user->name}",
                                'charge' => 0,
                            ]);
                        }
                    }
                }

                //Update investment progress
                $investment->received_count += 1;

                if ($package->duration > 0 && $investment->received_count >= $package->duration) {
                    $investment->status = 'completed';
                    $investment->next_return_date = null;
                } else {
                    $investment->next_return_date = $investment->return_type === 'daily'
                        ? Carbon::parse($investment->next_return_date)->addDay()
                        : Carbon::parse($investment->next_return_date)->addMonth();
                }

                $investment->save();

                DB::commit();

                $this->info("Investment #{$investment->id} processed successfully for {$user->name}");

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error processing investment #{$investment->id}: " . $e->getMessage());
                $this->error("Error processing investment #{$investment->id}: " . $e->getMessage());
            }
        }

        $this->info("Daily bonus & global income pool updated successfully.");
    }
}
