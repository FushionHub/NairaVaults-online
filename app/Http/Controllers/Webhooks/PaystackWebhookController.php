<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Traits\HandlesWebhookSignatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    use HandlesWebhookSignatures;

    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifyHmacSha512($request, config('services.paystack.webhook_secret'), 'X-Paystack-Signature')) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);
        $reference = $data['reference'] ?? '';

        $transaction = Transaction::where('reference', $reference)->first();
        if (! $transaction || $transaction->status === 'completed') {
            return response()->json(['message' => 'OK']);
        }

        if ($event === 'charge.success') {
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
