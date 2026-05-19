<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.gemini.base_url');
        $this->apiKey = config('services.gemini.api_key');
    }

    public function chat(string $userMessage, array $anonymisedContext = []): string
    {
        $systemPrompt = 'You are NairaVault AI, a helpful financial assistant for a Nigerian fintech platform. '
            .'Provide clear, concise financial guidance. '
            .'Never provide specific investment advice. '
            .'Context: '.json_encode($anonymisedContext);

        try {
            $response = Http::post($this->baseUrl.'/models/gemini-pro:generateContent?key='.$this->apiKey, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $systemPrompt."\n\nUser: ".$userMessage]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            $text = $response->json('candidates.0.content.parts.0.text', 'I apologize, I could not process your request.');

            return $text."\n\nThis is not regulated financial advice.";
        } catch (\Exception $e) {
            Log::error('Gemini API error', ['message' => $e->getMessage()]);

            return 'AI service is temporarily unavailable. Please try again later.';
        }
    }

    public function analyzeTransactions(array $anonymisedTransactions): string
    {
        $prompt = 'Analyze the following transaction patterns and provide financial insights. '
            .'Do not reference any personal information. Transactions: '
            .json_encode($anonymisedTransactions);

        return $this->chat($prompt);
    }
}
