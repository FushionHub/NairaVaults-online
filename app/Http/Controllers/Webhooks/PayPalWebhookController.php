<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource', []);

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $reference = $resource['custom_id'] ?? '';
            $transaction = Transaction::where('reference', $reference)->first();

            if ($transaction && $transaction->status !== 'completed') {
                $transaction->update([
                    'status' => 'completed',
                    'gateway_reference' => $resource['id'] ?? null,
                ]);
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
