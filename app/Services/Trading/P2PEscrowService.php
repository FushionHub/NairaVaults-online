<?php

namespace App\Services\Trading;

use App\Models\CryptoWallet;
use App\Models\P2pOffer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

class P2PEscrowService
{
    public function createOffer(User $user, array $data): P2pOffer
    {
        if ($data['direction'] === 'sell') {
            $wallet = CryptoWallet::select(CryptoWallet::safeSelect())
                ->where('user_id', $user->id)
                ->where('coin_symbol', $data['coin_symbol'])
                ->firstOrFail();

            if (! $wallet->hasBalance($data['amount'])) {
                throw new \InvalidArgumentException('Insufficient crypto balance');
            }

            $wallet->subtractBalance($data['amount']);
        }

        $offer = P2pOffer::create([
            'creator_user_id' => $user->id,
            'coin_symbol' => $data['coin_symbol'],
            'amount' => $data['amount'],
            'rate_per_unit' => $data['rate_per_unit'],
            'total_fiat' => bcmul($data['amount'], $data['rate_per_unit'], 8),
            'currency' => $data['currency'] ?? 'NGN',
            'direction' => $data['direction'],
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'escrow_reference' => 'ESC-' . Str::uuid(),
        ]);

        return $offer;
    }

    public function confirmTrade(P2pOffer $offer, User $counterparty): void
    {
        $offer->update([
            'counterparty_user_id' => $counterparty->id,
            'status' => 'completed',
        ]);

        if ($offer->direction === 'sell') {
            $wallet = CryptoWallet::select(CryptoWallet::safeSelect())
                ->where('user_id', $counterparty->id)
                ->where('coin_symbol', $offer->coin_symbol)
                ->first();

            if ($wallet) {
                $wallet->addBalance($offer->amount);
            }
        }
    }

    public function disputeTrade(P2pOffer $offer): void
    {
        $offer->update([
            'status' => 'disputed',
            'dispute_raised_at' => now(),
        ]);
    }

    public function cancelOffer(P2pOffer $offer): void
    {
        if ($offer->direction === 'sell') {
            $wallet = CryptoWallet::select(CryptoWallet::safeSelect())
                ->where('user_id', $offer->creator_user_id)
                ->where('coin_symbol', $offer->coin_symbol)
                ->first();

            if ($wallet) {
                $wallet->addBalance($offer->amount);
            }
        }

        $offer->update(['status' => 'cancelled']);
    }
}
