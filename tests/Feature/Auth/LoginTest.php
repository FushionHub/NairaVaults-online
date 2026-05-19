<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can login with valid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['token']);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'WrongPassword1!',
    ]);

    $response->assertStatus(401);
});

test('login is rate limited after 5 attempts', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword1!',
        ]);
    }

    $response->assertStatus(429);
});
