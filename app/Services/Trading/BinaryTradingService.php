<?php

namespace App\Services\Trading;

use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\UsesDecimalArithmetic;
use Illuminate\Support\Str;

class BinaryTradingService
{
    use UsesDecimalArithmetic;

    public function executeTrade(User $user, array $data): Transaction
    {
        $account = FiatAccount::where('user_id', $user->id)
            ->where('currency', $data['currency'] ?? 'NGN')
            ->firstOrFail();

        if (! $account->hasBalance($data['stake'])) {
            throw new \InvalidArgumentException('Insufficient balance for trade');
        }

        $account->subtractBalance($data['stake']);

        return Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $account->id,
            'type' => 'binary_trade',
            'amount' => $data['stake'],
            'currency' => $data['currency'] ?? 'NGN',
            'status' => 'pending',
            'reference' => 'BT-' . Str::uuid(),
            'direction' => 'debit',
            'metadata' => [
                'asset' => $data['asset'],
                'direction' => $data['direction'],
                'entry_price' => $data['entry_price'],
                'expiry_seconds' => $data['expiry_seconds'],
                'payout_multiplier' => $data['payout_multiplier'] ?? '1.85',
            ],
        ]);
    }

    public function resolveTrade(Transaction $trade, string $closingPrice): void
    {
        $metadata = $trade->metadata;
        $entryPrice = $metadata['entry_price'];
        $direction = $metadata['direction'];
        $payoutMultiplier = $metadata['payout_multiplier'] ?? '1.85';

        $won = ($direction === 'up' && bccomp($closingPrice, $entryPrice, 8) > 0)
            || ($direction === 'down' && bccomp($closingPrice, $entryPrice, 8) < 0);

        if ($won) {
            $payout = bcmul($trade->amount, $payoutMultiplier, 8);
            $account = FiatAccount::find($trade->transactionable_id);
            $account->addBalance($payout);

            $trade->update([
                'status' => 'completed',
                'metadata' => array_merge($metadata, [
                    'closing_price' => $closingPrice,
                    'result' => 'won',
                    'payout' => $payout,
                ]),
            ]);
        } else {
            $trade->update([
                'status' => 'completed',
                'metadata' => array_merge($metadata, [
                    'closing_price' => $closingPrice,
                    'result' => 'lost',
                    'payout' => '0',
                ]),
            ]);
        }
    }
}
