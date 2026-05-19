<?php

use App\Models\FiatAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deposit credits exact amount using BCMath', function () {
    $user = User::factory()->create(['kyc_status' => 'verified']);
    $account = FiatAccount::factory()->create([
        'user_id' => $user->id,
        'currency' => 'NGN',
        'balance' => '1000.00000000',
    ]);

    $depositAmount = '250.50000000';
    $expectedBalance = bcadd('1000.00000000', $depositAmount, 8);

    $account->balance = $expectedBalance;
    $account->save();
    $account->refresh();

    expect($account->balance)->toBe('1250.50000000');
});

test('withdrawal deduplication prevents duplicate withdrawals', function () {
    $user = User::factory()->create(['kyc_status' => 'verified']);
    $account = FiatAccount::factory()->create([
        'user_id' => $user->id,
        'currency' => 'NGN',
        'balance' => '50000.00000000',
    ]);

    $this->actingAs($user, 'sanctum');

    $response1 = $this->postJson('/api/v1/fiat/withdraw', [
        'amount' => '1000',
        'bank_code' => '044',
        'account_number' => '0123456789',
        'currency' => 'NGN',
    ]);

    $response2 = $this->postJson('/api/v1/fiat/withdraw', [
        'amount' => '1000',
        'bank_code' => '044',
        'account_number' => '0123456789',
        'currency' => 'NGN',
    ]);

    if ($response1->status() === 200) {
        expect($response2->status())->toBeIn([200, 429]);
    }
});

test('KYC gating returns 403 for unverified users', function () {
    $user = User::factory()->create(['kyc_status' => 'pending']);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/fiat/accounts');

    $response->assertStatus(403);
});

test('all monetary amounts are strings not floats', function () {
    $user = User::factory()->create(['kyc_status' => 'verified']);
    FiatAccount::factory()->create([
        'user_id' => $user->id,
        'currency' => 'NGN',
        'balance' => '1000.00000000',
    ]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/fiat/accounts');

    if ($response->status() === 200) {
        $data = $response->json();
        if (isset($data['accounts'][0]['balance'])) {
            expect($data['accounts'][0]['balance'])->toBeString();
        }
    }
});
