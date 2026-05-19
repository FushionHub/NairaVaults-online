<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Traits\HandlesWebhookSignatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KorapayWebhookController extends Controller
{
    use HandlesWebhookSignatures;

    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifyHmacSha512($request, config('services.korapay.webhook_secret'), 'X-Korapay-Signature')) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->input('data', []);
        $reference = $payload['reference'] ?? '';

        $transaction = Transaction::where('reference', $reference)->first();
        if (! $transaction || $transaction->status === 'completed') {
            return response()->json(['message' => 'OK']);
        }

        if (($payload['status'] ?? '') === 'success') {
            $transaction->update([
                'status' => 'completed',
                'gateway_reference' => $payload['transaction_reference'] ?? null,
            ]);

            if ($transaction->type === 'deposit' && $transaction->transactionable_id) {
                $account = FiatAccount::find($transaction->transactionable_id);
                $account?->addBalance($transaction->amount);
            }
        } else {
            $transaction->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
