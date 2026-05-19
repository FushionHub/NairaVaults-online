<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Jobs\ResolveBinaryTrade;
use App\Models\Transaction;
use App\Services\Crypto\BinanceService;
use App\Services\Trading\BinaryTradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BinaryTradingController extends Controller
{
    public function __construct(
        protected BinaryTradingService $tradingService,
        protected BinanceService $binanceService
    ) {}

    public function execute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => ['required', 'string'],
            'direction' => ['required', 'string', 'in:up,down'],
            'stake' => ['required', 'numeric', 'min:100'],
            'expiry_seconds' => ['required', 'integer', 'in:30,60,120,300'],
            'currency' => ['nullable', 'string', 'in:NGN,USD'],
        ]);

        $entryPrice = $this->binanceService->getLivePrice($validated['asset']);

        $validated['entry_price'] = $entryPrice;

        $trade = $this->tradingService->executeTrade($request->user(), $validated);

        ResolveBinaryTrade::dispatch($trade->id)->delay(now()->addSeconds($validated['expiry_seconds']));

        return response()->json([
            'trade' => $trade,
            'entry_price' => $entryPrice,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $trade = Transaction::where('type', 'binary_trade')->findOrFail($id);

        return response()->json(['trade' => $trade]);
    }

    public function history(Request $request): JsonResponse
    {
        $trades = Transaction::where('user_id', $request->user()->id)
            ->where('type', 'binary_trade')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($trades);
    }

    public function currentPrice(string $symbol): JsonResponse
    {
        $price = $this->binanceService->getLivePrice($symbol);

        return response()->json(['symbol' => $symbol, 'price' => $price]);
    }
}
