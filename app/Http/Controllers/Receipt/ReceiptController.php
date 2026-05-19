<?php

namespace App\Http\Controllers\Receipt;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\Statement;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $receipts = Receipt::where('user_id', $request->user()->id)
            ->with('transaction')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($receipts);
    }

    public function generate(int $transactionId, Request $request): JsonResponse
    {
        $transaction = Transaction::where('user_id', $request->user()->id)->findOrFail($transactionId);

        $receipt = Receipt::firstOrCreate(
            ['transaction_id' => $transaction->id, 'user_id' => $request->user()->id],
            ['receipt_number' => 'RCP-' . Str::uuid()]
        );

        return response()->json(['receipt' => $receipt->load('transaction')]);
    }

    public function generateStatement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:fiat,crypto,combined'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'format' => ['nullable', 'string', 'in:pdf,csv'],
        ]);

        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$validated['start_date'], $validated['end_date']])
            ->orderByDesc('created_at')
            ->get();

        $statement = Statement::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'format' => $validated['format'] ?? 'pdf',
        ]);

        return response()->json([
            'statement' => $statement,
            'transaction_count' => $transactions->count(),
        ]);
    }
}
