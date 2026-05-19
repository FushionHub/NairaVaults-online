<?php

namespace App\Services\Crypto;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrivyService
{
    protected string $baseUrl;
    protected string $appId;
    protected string $appSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.privy.base_url');
        $this->appId = config('services.privy.app_id');
        $this->appSecret = config('services.privy.app_secret');
    }

    public function createEmbeddedWallet(string $privyDid): array
    {
        return $this->makeRequest('POST', '/api/v1/wallets', [
            'user_id' => $privyDid,
            'chain_type' => 'ethereum',
        ]);
    }

    public function getWalletAddress(string $walletId): string
    {
        $response = $this->makeRequest('GET', "/api/v1/wallets/{$walletId}");

        return $response['address'] ?? '';
    }

    public function createUser(string $email): array
    {
        return $this->makeRequest('POST', '/api/v1/users', [
            'create_ethereum_wallet' => true,
            'linked_accounts' => [
                ['type' => 'email', 'address' => $email],
            ],
        ]);
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $request = Http::withBasicAuth($this->appId, $this->appSecret)
                ->withHeaders([
                    'privy-app-id' => $this->appId,
                    'Accept' => 'application/json',
                ])
                ->retry(3, 1000);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$endpoint, $data)
                : $request->post($this->baseUrl.$endpoint, $data);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Privy API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Wallet service unavailable'];
        }
    }
}
