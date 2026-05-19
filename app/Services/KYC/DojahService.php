<?php

namespace App\Services\KYC;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DojahService
{
    protected string $baseUrl;
    protected string $appId;
    protected string $privateKey;

    public function __construct()
    {
        $this->baseUrl = config('services.dojah.base_url');
        $this->appId = config('services.dojah.app_id');
        $this->privateKey = config('services.dojah.private_key');
    }

    public function verifyBVN(string $bvn, string $dob): array
    {
        $response = $this->makeRequest('GET', '/api/v1/kyc/bvn', [
            'bvn' => $bvn,
            'dob' => $dob,
        ]);

        return $response;
    }

    public function verifyNIN(string $nin): array
    {
        $response = $this->makeRequest('GET', '/api/v1/kyc/nin', [
            'nin' => $nin,
        ]);

        return $response;
    }

    public function livenessCheck(string $selfieBase64): array
    {
        $response = $this->makeRequest('POST', '/api/v1/ml/liveness', [
            'image' => $selfieBase64,
        ]);

        return $response;
    }

    public function verifyDocument(string $idType, string $idNumber, string $docImageBase64): array
    {
        $response = $this->makeRequest('POST', '/api/v1/document/verify', [
            'type' => $idType,
            'id_number' => $idNumber,
            'image' => $docImageBase64,
        ]);

        return $response;
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $request = Http::withHeaders([
                'AppId' => $this->appId,
                'Authorization' => $this->privateKey,
                'Accept' => 'application/json',
            ]);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$endpoint, $data)
                : $request->post($this->baseUrl.$endpoint, $data);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Dojah API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'KYC verification service unavailable'];
        }
    }
}
