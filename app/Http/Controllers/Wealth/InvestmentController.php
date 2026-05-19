<?php

namespace App\Http\Controllers\Wealth;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvestmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = InvestmentPlan::where('user_id', $request->user()->id)->get();

        $plans = $plans->map(function ($plan) {
            $daysElapsed = now()->diffInDays($plan->start_date);
            $dailyYield = bcdiv($plan->annual_yield, '365', 8);
            $accruedYield = bcmul(bcmul($plan->amount, $dailyYield, 8), (string) $daysElapsed, 8);
            $plan->current_value = bcadd($plan->amount, $accruedYield, 8);

            return $plan;
        });

        return response()->json(['plans' => $plans]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:5000'],
            'currency' => ['required', 'string', 'in:NGN,USD,USDT'],
            'duration_days' => ['required', 'integer', 'min:90'],
        ]);

        $user = $request->user();
        $account = FiatAccount::where('user_id', $user->id)
            ->where('currency', $validated['currency'])
            ->firstOrFail();

        if (! $account->hasBalance((string) $validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $account->subtractBalance((string) $validated['amount']);

        $annualYield = match (true) {
            $validated['duration_days'] >= 365 => '0.2000',
            $validated['duration_days'] >= 180 => '0.1500',
            default => '0.1000',
        };

        $plan = InvestmentPlan::create([
            'user_id' => $user->id,
            'plan_name' => $validated['plan_name'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'annual_yield' => $annualYield,
            'current_value' => $validated['amount'],
            'start_date' => now(),
            'maturity_date' => now()->addDays($validated['duration_days']),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'investment_lock',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'completed',
            'reference' => 'INV-' . Str::uuid(),
            'direction' => 'debit',
        ]);

        return response()->json(['plan' => $plan], 201);
    }
}
