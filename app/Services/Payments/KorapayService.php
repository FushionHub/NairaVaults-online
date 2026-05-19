<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KorapayService
{
    protected string $baseUrl;
    protected string $secretKey;
    protected string $publicKey;

    public function __construct()
    {
        $this->baseUrl = config('services.korapay.base_url');
        $this->secretKey = config('services.korapay.secret_key');
        $this->publicKey = config('services.korapay.public_key');
    }

    public function createVirtualAccount(array $data): array
    {
        return $this->makeRequest('POST', '/merchant/api/v1/virtual-bank-account', [
            'account_name' => $data['account_name'],
            'account_reference' => $data['reference'],
            'permanent' => true,
            'bank_code' => $data['bank_code'] ?? '035',
            'customer' => [
                'name' => $data['customer_name'],
                'email' => $data['customer_email'],
            ],
        ]);
    }

    public function initiateCharge(array $data): array
    {
        return $this->makeRequest('POST', '/merchant/api/v1/charges/initialize', [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $data['reference'],
            'customer' => [
                'email' => $data['email'],
                'name' => $data['name'],
            ],
            'notification_url' => config('app.url').'/api/v1/webhooks/korapay',
        ]);
    }

    public function initiateTransfer(array $data): array
    {
        return $this->makeRequest('POST', '/merchant/api/v1/transfers', [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $data['reference'],
            'destination' => [
                'type' => 'bank_account',
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'NGN',
                'bank_account' => [
                    'bank' => $data['bank_code'],
                    'account' => $data['account_number'],
                ],
            ],
        ]);
    }

    public function verifyTransaction(string $reference): array
    {
        return $this->makeRequest('GET', "/merchant/api/v1/charges/{$reference}");
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Accept' => 'application/json',
            ])->retry(3, 1000);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$endpoint, $data)
                : $request->post($this->baseUrl.$endpoint, $data);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Korapay API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Payment service unavailable'];
        }
    }
}
