<?php

namespace App\Http\Controllers\Fiat;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduledTransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transfers = ScheduledTransfer::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->orderBy('next_run_date')
            ->get();

        return response()->json(['transfers' => $transfers]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:NGN,USD,EUR,GBP,GHS',
            'recipient_email' => 'required|email',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $transfer = ScheduledTransfer::create([
            'user_id' => $request->user()->id,
            'from_account_type' => 'fiat',
            'to_account_identifier' => $request->recipient_email,
            'amount' => (string) $request->amount,
            'currency' => $request->currency,
            'frequency' => $request->frequency,
            'next_run_date' => $request->start_date,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Scheduled transfer created', 'transfer' => $transfer], 201);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        ScheduledTransfer::where('user_id', $request->user()->id)->findOrFail($id)
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Scheduled transfer cancelled']);
    }
}
