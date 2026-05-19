<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use App\Models\Transaction;
use App\Services\Crypto\BinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SwapController extends Controller
{
    public function getRate(Request $request, BinanceService $binanceService): JsonResponse
    {
        $request->validate([
            'from_coin' => 'required|string|max:20',
            'to_coin' => 'required|string|max:20',
        ]);

        $fromPrice = $binanceService->getLivePrice($request->from_coin.'USDT');
        $toPrice = $binanceService->getLivePrice($request->to_coin.'USDT');

        if (! $fromPrice || ! $toPrice) {
            return response()->json(['error' => 'Price data unavailable'], 503);
        }

        $rate = bcdiv($fromPrice, $toPrice, 8);

        return response()->json(['rate' => $rate, 'from_price_usd' => $fromPrice, 'to_price_usd' => $toPrice]);
    }

    public function execute(Request $request, BinanceService $binanceService): JsonResponse
    {
        $request->validate([
            'from_coin' => 'required|string|max:20',
            'to_coin' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        $user = $request->user();
        $fromWallet = CryptoWallet::where('user_id', $user->id)
            ->where('coin_symbol', strtoupper($request->from_coin))
            ->firstOrFail();

        if (! $fromWallet->hasBalance((string) $request->amount)) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $fromPrice = $binanceService->getLivePrice($request->from_coin.'USDT');
        $toPrice = $binanceService->getLivePrice($request->to_coin.'USDT');
        $toAmount = bcdiv(bcmul((string) $request->amount, $fromPrice, 8), $toPrice, 8);

        $fromWallet->subtractBalance((string) $request->amount);

        $toWallet = CryptoWallet::firstOrCreate(
            ['user_id' => $user->id, 'coin_symbol' => strtoupper($request->to_coin)],
            ['public_address' => '', 'balance' => '0.00000000', 'network' => 'mainnet']
        );
        $toWallet->addBalance($toAmount);

        $reference = 'SWAP-'.Str::uuid();
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_swap',
            'amount' => (string) $request->amount,
            'currency' => strtoupper($request->from_coin),
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => json_encode([
                'from_coin' => $request->from_coin,
                'to_coin' => $request->to_coin,
                'to_amount' => $toAmount,
            ]),
        ]);

        return response()->json(['message' => 'Swap completed', 'to_amount' => $toAmount, 'reference' => $reference]);
    }
}
