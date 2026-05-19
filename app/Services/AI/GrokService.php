<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.grok.base_url');
        $this->apiKey = config('services.grok.api_key');
    }

    public function analyseMarket(string $coinSymbol, array $priceHistory = []): string
    {
        $prompt = "Analyze the market for {$coinSymbol}. "
            .'Provide sentiment analysis, key support/resistance levels, and trading patterns. '
            .'Price history: '.json_encode(array_slice($priceHistory, -20));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/chat/completions', [
                'model' => 'grok-beta',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a crypto market analyst. Provide concise, data-driven analysis.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
                'max_tokens' => 1024,
            ]);

            $text = $response->json('choices.0.message.content', 'Market analysis unavailable.');

            return $text."\n\nThis is not regulated financial advice.";
        } catch (\Exception $e) {
            Log::error('Grok API error', ['message' => $e->getMessage()]);

            return 'Market analysis service is temporarily unavailable.';
        }
    }
}
