<?php

namespace App\Jobs;

use App\Models\FiatAccount;
use App\Models\SavingsPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMaturityPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $maturedPlans = SavingsPlan::where('status', 'active')
            ->where('maturity_date', '<=', now())
            ->get();

        foreach ($maturedPlans as $plan) {
            $interest = bcmul($plan->amount, $plan->interest_rate, 8);
            $payout = bcadd($plan->amount, $interest, 8);

            $account = FiatAccount::find($plan->fiat_account_id);
            $account?->addBalance($payout);

            $plan->update(['status' => 'completed']);
        }
    }
}
