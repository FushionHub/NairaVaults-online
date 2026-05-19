<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.paypal.base_url');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
    }

    public function createOrder(array $data): array
    {
        $token = $this->getAccessToken();

        return $this->makeRequest('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $data['reference'],
                'amount' => [
                    'currency_code' => $data['currency'] ?? 'USD',
                    'value' => $data['amount'],
                ],
            ]],
        ], $token);
    }

    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        return $this->makeRequest('POST', "/v2/checkout/orders/{$orderId}/capture", [], $token);
    }

    public function createPayout(array $data): array
    {
        $token = $this->getAccessToken();

        return $this->makeRequest('POST', '/v1/payments/payouts', [
            'sender_batch_header' => [
                'sender_batch_id' => $data['reference'],
                'email_subject' => 'NairaVault Withdrawal',
            ],
            'items' => [[
                'recipient_type' => 'EMAIL',
                'amount' => [
                    'value' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                ],
                'receiver' => $data['email'],
            ]],
        ], $token);
    }

    protected function getAccessToken(): string
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl.'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            return $response->json('access_token', '');
        } catch (\Exception $e) {
            Log::error('PayPal auth error', ['message' => $e->getMessage()]);

            return '';
        }
    }

    protected function makeRequest(string $method, string $endpoint, array $data = [], string $token = ''): array
    {
        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

            $response = $method === 'GET'
                ? $request->get($this->baseUrl.$endpoint, $data)
                : $request->post($this->baseUrl.$endpoint, $data);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('PayPal API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'PayPal service unavailable'];
        }
    }
}
