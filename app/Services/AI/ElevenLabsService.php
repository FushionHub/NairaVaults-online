<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $voiceId;

    public function __construct()
    {
        $this->baseUrl = config('services.elevenlabs.base_url');
        $this->apiKey = config('services.elevenlabs.api_key');
        $this->voiceId = config('services.elevenlabs.voice_id');
    }

    public function textToSpeech(string $text, ?string $voiceId = null): string
    {
        $voice = $voiceId ?? $this->voiceId;

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'audio/mpeg',
            ])->post($this->baseUrl."/text-to-speech/{$voice}", [
                'text' => $text,
                'model_id' => 'eleven_monolingual_v1',
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                ],
            ]);

            return base64_encode($response->body());
        } catch (\Exception $e) {
            Log::error('ElevenLabs API error', ['message' => $e->getMessage()]);

            return '';
        }
    }

    public function getVoices(): array
    {
        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])->get($this->baseUrl.'/voices');

            return $response->json('voices', []);
        } catch (\Exception $e) {
            Log::error('ElevenLabs voices error', ['message' => $e->getMessage()]);

            return [];
        }
    }
}
