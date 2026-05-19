<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Traits\HandlesWebhookSignatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlutterwaveWebhookController extends Controller
{
    use HandlesWebhookSignatures;

    public function handle(Request $request): JsonResponse
    {
        $secretHash = config('services.flutterwave.webhook_secret');
        $signature = $request->header('verif-hash');

        if (! $signature || $signature !== $secretHash) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $data = $request->input('data', []);
        $reference = $data['tx_ref'] ?? '';

        $transaction = Transaction::where('reference', $reference)->first();
        if (! $transaction || $transaction->status === 'completed') {
            return response()->json(['message' => 'OK']);
        }

        if (($data['status'] ?? '') === 'successful') {
            $transaction->update([
                'status' => 'completed',
                'gateway_reference' => $data['id'] ?? null,
            ]);

            if ($transaction->type === 'deposit' && $transaction->transactionable_id) {
                $account = FiatAccount::find($transaction->transactionable_id);
                $account?->addBalance($transaction->amount);
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
