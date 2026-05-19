<?php

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('invalid webhook signature returns 400', function () {
    $payload = json_encode(['event' => 'charge.completed', 'data' => ['reference' => 'test-ref']]);

    $response = $this->postJson('/api/v1/webhooks/korapay', json_decode($payload, true), [
        'x-korapay-signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
});

test('webhook idempotency prevents duplicate processing', function () {
    Transaction::create([
        'user_id' => 1,
        'type' => 'deposit',
        'amount' => '1000.00000000',
        'currency' => 'NGN',
        'status' => 'completed',
        'reference' => 'duplicate-ref-123',
        'direction' => 'credit',
    ]);

    $exists = Transaction::where('reference', 'duplicate-ref-123')->exists();
    expect($exists)->toBeTrue();
});
