<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\RecurringPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringPurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = RecurringPurchase::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['plans' => $plans]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:NGN,USD,EUR,GBP,GHS',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly',
        ]);

        $nextRun = match ($request->frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'biweekly' => now()->addWeeks(2),
            'monthly' => now()->addMonth(),
        };

        $plan = RecurringPurchase::create([
            'user_id' => $request->user()->id,
            'coin_symbol' => strtoupper($request->coin_symbol),
            'amount' => (string) $request->amount,
            'currency' => $request->currency,
            'frequency' => $request->frequency,
            'next_run_date' => $nextRun,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'DCA plan created', 'plan' => $plan], 201);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $plan = RecurringPurchase::where('user_id', $request->user()->id)->findOrFail($id);
        $plan->update(['status' => 'cancelled']);

        return response()->json(['message' => 'DCA plan cancelled']);
    }
}
