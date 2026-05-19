<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\ElevenLabsService;
use App\Services\AI\GeminiService;
use App\Services\AI\GrokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIAssistantController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService,
        protected GrokService $grokService,
        protected ElevenLabsService $elevenLabsService
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'in:general,market,analysis'],
        ]);

        $user = $request->user();
        $context = [
            'kyc_status' => $user->kyc_status,
            'account_type' => $user->account_type,
            'preferred_currency' => $user->preferred_currency,
        ];

        $type = $validated['type'] ?? 'general';

        $response = match ($type) {
            'market' => $this->grokService->analyseMarket($validated['message']),
            default => $this->geminiService->chat($validated['message'], $context),
        };

        return response()->json(['response' => $response]);
    }

    public function processVoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'voice_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $context = [
            'kyc_status' => $user->kyc_status,
            'preferred_currency' => $user->preferred_currency,
        ];

        $aiResponse = $this->geminiService->chat($validated['text'], $context);
        $audio = $this->elevenLabsService->textToSpeech($aiResponse, $validated['voice_id'] ?? null);

        return response()->json([
            'text_response' => $aiResponse,
            'audio' => $audio,
        ]);
    }

    public function voices(): JsonResponse
    {
        $voices = $this->elevenLabsService->getVoices();

        return response()->json(['voices' => $voices]);
    }
}
