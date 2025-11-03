<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Investor;
use App\Models\GlobalIncomeSetting;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributeGlobalIncome extends Command
{
    protected $signature = 'distribute:global-income';
    protected $description = 'Check eligibility and distribute global income daily';

    public function handle()
    {
        $this->info("🚀 Starting Global Income Check & Distribution...");

        $settings = GlobalIncomeSetting::first();
        if (!$settings) {
            $this->error("❌ Global Income Settings not found.");
            return;
        }

        // Step 1: Check eligibility for users who are not yet eligible
        $newEligibleUsers = User::where('role', 'user')
                                ->where('global_income', 0)
                                ->get();

        foreach ($newEligibleUsers as $user) {
            $directReferrals = $user->referrals()->pluck('id');
            $directInvest = Investor::whereIn('user_id', $directReferrals)->sum('amount');

            $teamIds = $this->getTeamIds($user->id, 5);
            $teamInvest = Investor::whereIn('user_id', $teamIds)->sum('amount');

            if ($directInvest >= $settings->min_direct_ref_invest &&
                $teamInvest >= $settings->min_team_invest) {
                $user->update(['global_income' => 1]);
                $this->info("✅ {$user->name} is now eligible for Global Income.");
            }
        }

        // Step 2: Get all eligible users
        $eligibleUsers = User::where('global_income', 1)->get();
        $eligibleCount = $eligibleUsers->count();

        if ($eligibleCount === 0) {
            DB::table('global_income_pools')->truncate();
            $this->info("ℹ No eligible users today. Pool reset to 0.");
            return;
        }

        // Step 3: Get total pool
        $totalPool = DB::table('global_income_pools')->sum('amount');
        if ($totalPool <= 0) {
            $this->info("ℹ No global income pool available today.");
            return;
        }

        $share = round($totalPool / $eligibleCount, 8);

        // Step 4: Distribute using DB::transaction()
        try {
            DB::transaction(function() use ($eligibleUsers, $share) {
                foreach ($eligibleUsers as $user) {
                    $user->increment('spot_wallet', $share);

                    Transactions::create([
                        'transaction_id' => Transactions::generateTransactionId(),
                        'user_id' => $user->id,
                        'amount' => $share,
                        'remark' => 'global_income',
                        'type' => '+',
                        'status' => 'Paid',
                        'details' => 'Daily Global Income Distribution',
                        'charge' => 0,
                    ]);
                }

                DB::table('global_income_pools')->delete();
            });

            $this->info("🎉 Distributed {$share} to {$eligibleCount} eligible users successfully.");

        } catch (\Throwable $e) {
            Log::error("❌ Global income distribution failed: " . $e->getMessage());
            $this->error("Error during distribution: " . $e->getMessage());
        }
    }

    /**
     * Recursively get team members up to N levels deep
     */
    private function getTeamIds($userId, $levels = 5)
    {
        $team = collect();
        $currentLevel = collect([$userId]);

        for ($i = 1; $i <= $levels; $i++) {
            $nextLevel = User::whereIn('refer_by', $currentLevel)->pluck('id');
            if ($nextLevel->isEmpty()) break;

            $team = $team->merge($nextLevel);
            $currentLevel = $nextLevel;
        }

        return $team->unique()->values();
    }
}
