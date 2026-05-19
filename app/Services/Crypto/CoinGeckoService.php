<?php

namespace App\Services\Crypto;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoinGeckoService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.coingecko.base_url');
        $this->apiKey = config('services.coingecko.api_key');
    }

    public function getMarketData(array $coinIds, string $vsCurrency = 'usd'): array
    {
        $cacheKey = 'coingecko_market:'.implode(',', $coinIds).':'.$vsCurrency;

        return Cache::remember($cacheKey, 300, function () use ($coinIds, $vsCurrency) {
            return $this->makeRequest('GET', '/coins/markets', [
                'vs_currency' => $vsCurrency,
                'ids' => implode(',', $coinIds),
                'order' => 'market_cap_desc',
                'sparkline' => 'false',
            ]);
        });
    }

    public function getCoinDetail(string $coinId): array
    {
        return $this->makeRequest('GET', "/coins/{$coinId}", [
            'localization' => 'false',
            'tickers' => 'false',
            'community_data' => 'false',
            'developer_data' => 'false',
        ]);
    }

    public function getSimplePrice(array $coinIds, string $vsCurrency = 'usd'): array
    {
        return $this->makeRequest('GET', '/simple/price', [
            'ids' => implode(',', $coinIds),
            'vs_currencies' => $vsCurrency,
            'include_24hr_change' => 'true',
            'include_market_cap' => 'true',
        ]);
    }

    protected function makeRequest(string $method, string $endpoint, array $params = []): array
    {
        try {
            $headers = ['Accept' => 'application/json'];
            if ($this->apiKey) {
                $headers['x-cg-demo-api-key'] = $this->apiKey;
            }

            $response = Http::withHeaders($headers)
                ->retry(3, 1000)
                ->get($this->baseUrl.$endpoint, $params);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('CoinGecko API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Market data unavailable'];
        }
    }
}
