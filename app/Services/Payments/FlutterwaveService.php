<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.flutterwave.base_url');
        $this->secretKey = config('services.flutterwave.secret_key');
    }

    public function initiatePayment(array $data): array
    {
        return $this->makeRequest('POST', '/payments', [
            'tx_ref' => $data['reference'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'redirect_url' => $data['redirect_url'] ?? config('app.frontend_url').'/fiat/deposit/callback',
            'customer' => [
                'email' => $data['email'],
                'name' => $data['name'],
            ],
            'payment_options' => $data['payment_options'] ?? 'card,banktransfer',
        ]);
    }

    public function verifyTransaction(string $transactionId): array
    {
        return $this->makeRequest('GET', "/transactions/{$transactionId}/verify");
    }

    public function initiateTransfer(array $data): array
    {
        return $this->makeRequest('POST', '/transfers', [
            'account_bank' => $data['bank_code'],
            'account_number' => $data['account_number'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $data['reference'],
            'narration' => $data['narration'] ?? 'NairaVault Transfer',
        ]);
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
            Log::error('Flutterwave API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Payment service unavailable'];
        }
    }
}
