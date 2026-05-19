<?php

namespace App\Http\Controllers\Wealth;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\SavingsPlan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SavingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = SavingsPlan::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['plans' => $plans]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'currency' => ['required', 'string', 'in:NGN,USD,USDT'],
            'duration_days' => ['required', 'integer', 'min:30'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $account = FiatAccount::where('user_id', $user->id)
            ->where('currency', $validated['currency'])
            ->firstOrFail();

        if (! $account->hasBalance((string) $validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $account->subtractBalance((string) $validated['amount']);

        $interestRate = match (true) {
            $validated['duration_days'] >= 365 => '0.1500',
            $validated['duration_days'] >= 180 => '0.1200',
            $validated['duration_days'] >= 90 => '0.0800',
            default => '0.0500',
        };

        $plan = SavingsPlan::create([
            'user_id' => $user->id,
            'fiat_account_id' => $account->id,
            'name' => $validated['name'] ?? 'Savings Plan',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'interest_rate' => $interestRate,
            'start_date' => now(),
            'maturity_date' => now()->addDays($validated['duration_days']),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'savings_lock',
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'completed',
            'reference' => 'SAV-' . Str::uuid(),
            'direction' => 'debit',
        ]);

        return response()->json(['plan' => $plan], 201);
    }

    public function withdrawEarly(int $id, Request $request): JsonResponse
    {
        $plan = SavingsPlan::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->findOrFail($id);

        $penalty = bcmul($plan->amount, $plan->penalty_rate, 8);
        $payout = bcsub($plan->amount, $penalty, 8);

        $account = FiatAccount::findOrFail($plan->fiat_account_id);
        $account->addBalance($payout);

        $plan->update(['status' => 'withdrawn_early']);

        Transaction::create([
            'user_id' => $request->user()->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'savings_unlock',
            'amount' => $payout,
            'currency' => $plan->currency,
            'fee' => $penalty,
            'status' => 'completed',
            'reference' => 'SAVW-' . Str::uuid(),
            'direction' => 'credit',
        ]);

        return response()->json([
            'message' => 'Early withdrawal processed',
            'payout' => $payout,
            'penalty' => $penalty,
        ]);
    }
}
