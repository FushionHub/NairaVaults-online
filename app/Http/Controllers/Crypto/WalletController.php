<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use App\Models\FiatAccount;
use App\Models\Transaction;
use App\Services\Crypto\BinanceService;
use App\Services\Crypto\CoinGeckoService;
use App\Services\Crypto\PrivyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index(Request $request, BinanceService $binanceService): JsonResponse
    {
        $wallets = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $request->user()->id)
            ->get();

        $symbols = $wallets->pluck('coin_symbol')->toArray();
        $prices = $binanceService->getMultiplePrices($symbols);

        $wallets = $wallets->map(function ($wallet) use ($prices) {
            $wallet->current_price = $prices[$wallet->coin_symbol] ?? '0';
            $wallet->value_usd = bcmul($wallet->balance, $wallet->current_price, 8);

            return $wallet;
        });

        return response()->json(['wallets' => $wallets]);
    }

    public function store(Request $request, PrivyService $privyService): JsonResponse
    {
        $validated = $request->validate([
            'coin_symbol' => ['required', 'string', 'in:BTC,ETH,BNB,USDT,SOL,MATIC'],
        ]);

        $user = $request->user();

        $existing = CryptoWallet::where('user_id', $user->id)
            ->where('coin_symbol', $validated['coin_symbol'])
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Wallet already exists'], 409);
        }

        $privyResult = $privyService->createEmbeddedWallet($user->privy_did ?? $user->email);

        $wallet = CryptoWallet::create([
            'user_id' => $user->id,
            'coin_symbol' => $validated['coin_symbol'],
            'public_address' => $privyResult['address'] ?? '0x'.Str::random(40),
            'encrypted_priv_key' => Crypt::encryptString($privyResult['private_key'] ?? Str::random(64)),
            'privy_wallet_id' => $privyResult['id'] ?? null,
            'balance' => '0.00000000',
        ]);

        return response()->json([
            'wallet' => $wallet->only(CryptoWallet::safeSelect()),
        ], 201);
    }

    public function buy(Request $request, BinanceService $binanceService): JsonResponse
    {
        $validated = $request->validate([
            'coin_symbol' => ['required', 'string'],
            'amount_fiat' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'in:NGN,USD'],
        ]);

        $user = $request->user();
        $fiatAccount = FiatAccount::where('user_id', $user->id)
            ->where('currency', $validated['currency'])
            ->firstOrFail();

        if (! $fiatAccount->hasBalance((string) $validated['amount_fiat'])) {
            return response()->json(['error' => 'Insufficient fiat balance'], 400);
        }

        $price = $binanceService->getLivePrice($validated['coin_symbol']);
        $cryptoAmount = bcdiv((string) $validated['amount_fiat'], $price, 8);

        $fiatAccount->subtractBalance((string) $validated['amount_fiat']);

        $wallet = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)
            ->where('coin_symbol', $validated['coin_symbol'])
            ->firstOrFail();

        $wallet->addBalance($cryptoAmount);

        $reference = 'BUY-'.Str::uuid();

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => FiatAccount::class,
            'transactionable_id' => $fiatAccount->id,
            'type' => 'crypto_buy',
            'amount' => $validated['amount_fiat'],
            'currency' => $validated['currency'],
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => [
                'coin_symbol' => $validated['coin_symbol'],
                'crypto_amount' => $cryptoAmount,
                'rate' => $price,
            ],
        ]);

        return response()->json([
            'message' => 'Purchase completed',
            'crypto_amount' => $cryptoAmount,
            'reference' => $reference,
        ]);
    }

    public function swap(Request $request, BinanceService $binanceService): JsonResponse
    {
        $validated = $request->validate([
            'from_coin' => ['required', 'string'],
            'to_coin' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $user = $request->user();

        $fromWallet = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)
            ->where('coin_symbol', $validated['from_coin'])
            ->firstOrFail();

        if (! $fromWallet->hasBalance((string) $validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $fromPrice = $binanceService->getLivePrice($validated['from_coin']);
        $toPrice = $binanceService->getLivePrice($validated['to_coin']);

        $usdValue = bcmul((string) $validated['amount'], $fromPrice, 8);
        $toAmount = bcdiv($usdValue, $toPrice, 8);

        $fromWallet->subtractBalance((string) $validated['amount']);

        $toWallet = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)
            ->where('coin_symbol', $validated['to_coin'])
            ->firstOrFail();

        $toWallet->addBalance($toAmount);

        $reference = 'SWAP-'.Str::uuid();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'swap',
            'amount' => $validated['amount'],
            'currency' => $validated['from_coin'],
            'status' => 'completed',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => [
                'from_coin' => $validated['from_coin'],
                'to_coin' => $validated['to_coin'],
                'to_amount' => $toAmount,
                'rate' => bcdiv($fromPrice, $toPrice, 8),
            ],
        ]);

        return response()->json([
            'message' => 'Swap completed',
            'to_amount' => $toAmount,
            'reference' => $reference,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coin_symbol' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'to_address' => ['required', 'string'],
        ]);

        $user = $request->user();
        $wallet = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)
            ->where('coin_symbol', $validated['coin_symbol'])
            ->firstOrFail();

        if (! $wallet->hasBalance((string) $validated['amount'])) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $wallet->subtractBalance((string) $validated['amount']);
        $reference = 'SEND-'.Str::uuid();

        Transaction::create([
            'user_id' => $user->id,
            'transactionable_type' => CryptoWallet::class,
            'transactionable_id' => $wallet->id,
            'type' => 'crypto_send',
            'amount' => $validated['amount'],
            'currency' => $validated['coin_symbol'],
            'status' => 'processing',
            'reference' => $reference,
            'direction' => 'debit',
            'metadata' => ['to_address' => $validated['to_address']],
        ]);

        return response()->json([
            'message' => 'Transfer initiated',
            'reference' => $reference,
        ]);
    }

    public function importWallet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coin_symbol' => ['required', 'string'],
            'public_address' => ['required', 'string'],
            'encrypted_key' => ['required', 'string'],
        ]);

        $user = $request->user();

        $wallet = CryptoWallet::create([
            'user_id' => $user->id,
            'coin_symbol' => $validated['coin_symbol'],
            'public_address' => $validated['public_address'],
            'encrypted_priv_key' => Crypt::encryptString($validated['encrypted_key']),
            'imported' => true,
            'balance' => '0.00000000',
        ]);

        return response()->json([
            'wallet' => $wallet->only(CryptoWallet::safeSelect()),
        ], 201);
    }

    public function marketData(string $coinId, BinanceService $binanceService, CoinGeckoService $coinGeckoService): JsonResponse
    {
        $price = $binanceService->getLivePrice($coinId);
        $ohlcv = $binanceService->getOHLCV($coinId);
        $detail = $coinGeckoService->getCoinDetail(strtolower($coinId));

        return response()->json([
            'price' => $price,
            'ohlcv' => $ohlcv,
            'detail' => $detail,
        ]);
    }

    public function priceStream(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->stream(function () {
            $binanceService = app(BinanceService::class);
            $symbols = ['BTC', 'ETH', 'BNB', 'USDT', 'SOL', 'MATIC'];

            while (true) {
                $prices = $binanceService->getMultiplePrices($symbols);

                echo 'data: '.json_encode(['prices' => $prices, 'timestamp' => now()->toIso8601String()])."\n\n";
                ob_flush();
                flush();

                sleep(5);

                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
