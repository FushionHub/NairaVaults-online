<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use App\Models\Transaction;
use App\Services\Crypto\BinanceService;
use App\Traits\UsesDecimalArithmetic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CryptoTransactionController extends Controller
{
    use UsesDecimalArithmetic;

    public function buy(Request $request, BinanceService $binanceService): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'amount_fiat' => 'required|numeric|min:0.01',
            'currency' => 'required|in:NGN,USD,EUR,GBP,GHS',
        ]);

        $user = $request->user();
        $fiatAccount = $user->fiatAccounts()->where('currency', $request->currency)->firstOrFail();

        if (! $fiatAccount->hasBalance($request->amount_fiat)) {
            return response()->json(['error' => 'Insufficient balance', 'code' => 'INSUFFICIENT_BALANCE'], 400);
        }

        $price = $binanceService->getLivePrice($request->coin_symbol.'USDT');
        if (! $price) {
            return response()->json(['error' => 'Price unavailable', 'code' => 'PRICE_UNAVAILABLE'], 503);
        }

        $cryptoAmount = bcdiv((string) $request->amount_fiat, $price, 8);
        $fiatAccount->subtractBalance((string) $request->amount_fiat);

        $wallet = CryptoWallet::firstOrCreate(
            ['user_id' => $user->id, 'coin_symbol' => strtoupper($request->coin_symbol)],
            ['public_address' => '', 'balance' => '0.00000000', 'network' => 'mainnet']
        );
        $wallet->addBalance($cryptoAmount);

        $reference = 'BUY-'.Str::uuid();
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_buy',
            'amount' => (string) $request->amount_fiat,
            'currency' => $request->currency,
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => json_encode([
                'coin_symbol' => $request->coin_symbol,
                'crypto_amount' => $cryptoAmount,
                'price' => $price,
            ]),
        ]);

        return response()->json([
            'message' => 'Purchase completed',
            'crypto_amount' => $cryptoAmount,
            'reference' => $reference,
        ]);
    }

    public function sell(Request $request, BinanceService $binanceService): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.00000001',
            'currency' => 'required|in:NGN,USD,EUR,GBP,GHS',
        ]);

        $user = $request->user();
        $wallet = CryptoWallet::where('user_id', $user->id)
            ->where('coin_symbol', strtoupper($request->coin_symbol))
            ->firstOrFail();

        if (! $wallet->hasBalance((string) $request->amount)) {
            return response()->json(['error' => 'Insufficient crypto balance', 'code' => 'INSUFFICIENT_BALANCE'], 400);
        }

        $price = $binanceService->getLivePrice($request->coin_symbol.'USDT');
        $fiatAmount = bcmul((string) $request->amount, $price, 8);

        $wallet->subtractBalance((string) $request->amount);
        $fiatAccount = $user->fiatAccounts()->where('currency', $request->currency)->firstOrFail();
        $fiatAccount->addBalance($fiatAmount);

        $reference = 'SELL-'.Str::uuid();
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_sell',
            'amount' => $fiatAmount,
            'currency' => $request->currency,
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'credit',
        ]);

        return response()->json(['message' => 'Sale completed', 'fiat_amount' => $fiatAmount, 'reference' => $reference]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.00000001',
            'to_address' => 'required|string',
        ]);

        $user = $request->user();
        $wallet = CryptoWallet::where('user_id', $user->id)
            ->where('coin_symbol', strtoupper($request->coin_symbol))
            ->firstOrFail();

        if (! $wallet->hasBalance((string) $request->amount)) {
            return response()->json(['error' => 'Insufficient balance', 'code' => 'INSUFFICIENT_BALANCE'], 400);
        }

        $wallet->subtractBalance((string) $request->amount);

        $reference = 'SEND-'.Str::uuid();
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'crypto_send',
            'amount' => (string) $request->amount,
            'currency' => strtoupper($request->coin_symbol),
            'status' => 'pending',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => json_encode(['to_address' => $request->to_address]),
        ]);

        return response()->json(['message' => 'Transfer initiated', 'reference' => $reference]);
    }
}
