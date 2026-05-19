<?php

namespace App\Services\Crypto;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BinanceService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.binance.base_url');
        $this->apiKey = config('services.binance.api_key');
        $this->secretKey = config('services.binance.secret_key');
    }

    public function getLivePrice(string $symbol): string
    {
        $cacheKey = "binance_price:{$symbol}";

        return Cache::remember($cacheKey, 30, function () use ($symbol) {
            $response = $this->makePublicRequest('GET', '/api/v3/ticker/price', [
                'symbol' => strtoupper($symbol).'USDT',
            ]);

            return $response['price'] ?? '0.00000000';
        });
    }

    public function getMultiplePrices(array $symbols): array
    {
        $response = $this->makePublicRequest('GET', '/api/v3/ticker/price');

        if (isset($response['error'])) {
            return [];
        }

        $prices = [];
        $symbolMap = array_flip(array_map(fn ($s) => strtoupper($s).'USDT', $symbols));

        foreach ($response as $ticker) {
            if (isset($symbolMap[$ticker['symbol']])) {
                $prices[str_replace('USDT', '', $ticker['symbol'])] = $ticker['price'];
            }
        }

        return $prices;
    }

    public function getOHLCV(string $symbol, string $interval = '1h', int $limit = 100): array
    {
        $response = $this->makePublicRequest('GET', '/api/v3/klines', [
            'symbol' => strtoupper($symbol).'USDT',
            'interval' => $interval,
            'limit' => $limit,
        ]);

        if (isset($response['error'])) {
            return [];
        }

        return array_map(fn ($candle) => [
            'time' => $candle[0],
            'open' => $candle[1],
            'high' => $candle[2],
            'low' => $candle[3],
            'close' => $candle[4],
            'volume' => $candle[5],
        ], $response);
    }

    public function createSpotOrder(string $symbol, string $side, string $quantity): array
    {
        $params = [
            'symbol' => strtoupper($symbol).'USDT',
            'side' => strtoupper($side),
            'type' => 'MARKET',
            'quantity' => $quantity,
            'timestamp' => now()->timestamp * 1000,
        ];

        $params['signature'] = $this->generateSignature($params);

        return $this->makeSignedRequest('POST', '/api/v3/order', $params);
    }

    protected function generateSignature(array $params): string
    {
        $queryString = http_build_query($params);

        return hash_hmac('sha256', $queryString, $this->secretKey);
    }

    protected function makePublicRequest(string $method, string $endpoint, array $params = []): array
    {
        try {
            $response = Http::retry(3, 1000)
                ->get($this->baseUrl.$endpoint, $params);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Binance API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Market data unavailable'];
        }
    }

    protected function makeSignedRequest(string $method, string $endpoint, array $params = []): array
    {
        try {
            $response = Http::withHeaders([
                'X-MBX-APIKEY' => $this->apiKey,
            ])->retry(3, 1000)
                ->post($this->baseUrl.$endpoint, $params);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Binance API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Trade execution unavailable'];
        }
    }
}
