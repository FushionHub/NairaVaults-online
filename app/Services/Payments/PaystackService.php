<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.paystack.base_url');
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function getBankList(): array
    {
        return $this->makeRequest('GET', '/bank?country=nigeria');
    }

    public function resolveAccountNumber(string $accountNumber, string $bankCode): array
    {
        return $this->makeRequest('GET', '/bank/resolve', [
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
        ]);
    }

    public function initializeTransaction(array $data): array
    {
        return $this->makeRequest('POST', '/transaction/initialize', [
            'amount' => bcmul($data['amount'], '100', 0),
            'email' => $data['email'],
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $data['reference'],
            'callback_url' => $data['callback_url'] ?? null,
        ]);
    }

    public function verifyTransaction(string $reference): array
    {
        return $this->makeRequest('GET', "/transaction/verify/{$reference}");
    }

    public function initiateTransfer(array $data): array
    {
        $recipientCode = $this->createTransferRecipient($data);

        if (isset($recipientCode['error'])) {
            return $recipientCode;
        }

        return $this->makeRequest('POST', '/transfer', [
            'source' => 'balance',
            'amount' => bcmul($data['amount'], '100', 0),
            'recipient' => $recipientCode['data']['recipient_code'] ?? '',
            'reason' => $data['reason'] ?? 'NairaVault Transfer',
            'reference' => $data['reference'],
        ]);
    }

    protected function createTransferRecipient(array $data): array
    {
        return $this->makeRequest('POST', '/transferrecipient', [
            'type' => 'nuban',
            'name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'bank_code' => $data['bank_code'],
            'currency' => 'NGN',
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
            Log::error('Paystack API error', ['message' => $e->getMessage()]);

            return ['error' => true, 'message' => 'Payment service unavailable'];
        }
    }
}
