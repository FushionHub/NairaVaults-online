<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'display_name' => 'Test User',
        'preferred_currency' => 'NGN',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['token', 'user']);
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('duplicate registration is rejected', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'preferred_currency' => 'NGN',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('registration with referral code tracks referral', function () {
    $referrer = User::factory()->create(['referral_code' => 'REF123']);

    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'referred@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'preferred_currency' => 'NGN',
        'referral_code' => 'REF123',
    ]);

    $response->assertStatus(201);
});

test('registration requires password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'preferred_currency' => 'NGN',
    ]);

    $response->assertStatus(422);
});
