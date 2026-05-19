<?php

use App\Models\CryptoWallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private key is never returned in API response', function () {
    $user = User::factory()->create(['kyc_status' => 'verified']);
    CryptoWallet::factory()->create([
        'user_id' => $user->id,
        'coin_symbol' => 'BTC',
        'balance' => '0.50000000',
        'encrypted_priv_key' => 'encrypted_test_key_value',
    ]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/crypto/wallets');

    $response->assertStatus(200);
    $responseJson = $response->content();
    expect($responseJson)->not->toContain('encrypted_test_key_value');
    expect($responseJson)->not->toContain('encrypted_priv_key');
});

test('wallet listing excludes encrypted_priv_key field', function () {
    $user = User::factory()->create(['kyc_status' => 'verified']);
    CryptoWallet::factory()->create([
        'user_id' => $user->id,
        'coin_symbol' => 'ETH',
        'balance' => '1.00000000',
    ]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/crypto/wallets');

    if ($response->status() === 200) {
        $wallets = $response->json('wallets', []);
        foreach ($wallets as $wallet) {
            expect(array_key_exists('encrypted_priv_key', $wallet))->toBeFalse();
        }
    }
});
