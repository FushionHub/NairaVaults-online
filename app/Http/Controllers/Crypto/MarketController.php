<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Services\Crypto\BinanceService;
use App\Services\Crypto\CoinGeckoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketController extends Controller
{
    public function prices(Request $request, BinanceService $binanceService): JsonResponse
    {
        $symbols = explode(',', $request->query('symbols', 'BTCUSDT,ETHUSDT'));
        $prices = $binanceService->getMultiplePrices($symbols);

        return response()->json(['prices' => $prices]);
    }

    public function ohlcv(Request $request, BinanceService $binanceService): JsonResponse
    {
        $request->validate([
            'symbol' => 'required|string',
            'interval' => 'in:1m,5m,15m,1h,4h,1d|nullable',
            'limit' => 'integer|min:1|max:500|nullable',
        ]);

        $data = $binanceService->getOHLCV(
            $request->symbol,
            $request->query('interval', '1h'),
            (int) $request->query('limit', 100)
        );

        return response()->json(['candles' => $data]);
    }

    public function priceStream(Request $request, BinanceService $binanceService): StreamedResponse
    {
        $symbols = explode(',', $request->query('symbols', 'BTCUSDT,ETHUSDT'));

        return response()->stream(function () use ($symbols, $binanceService) {
            while (true) {
                foreach ($symbols as $symbol) {
                    $price = $binanceService->getLivePrice(trim($symbol));
                    if ($price) {
                        echo "data: ".json_encode(['symbol' => trim($symbol), 'price' => $price])."\n\n";
                    }
                }
                ob_flush();
                flush();
                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function coinDetail(string $coinId, CoinGeckoService $coinGeckoService): JsonResponse
    {
        $data = $coinGeckoService->getCoinDetail($coinId);

        return response()->json($data);
    }
}
