<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'display_name' => ['nullable', 'string', 'max:255'],
            'preferred_currency' => ['nullable', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);

        $referredBy = null;
        if (! empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            $referredBy = $referrer?->id;
        }

        $user = User::create([
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'display_name' => $validated['display_name'] ?? null,
            'preferred_currency' => $validated['preferred_currency'] ?? 'NGN',
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referredBy,
        ]);

        FiatAccount::create([
            'user_id' => $user->id,
            'currency' => $user->preferred_currency,
            'balance' => '0.00000000',
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => $user->only(['id', 'email', 'display_name', 'preferred_currency', 'referral_code']),
            'token' => $token,
        ], 201);
    }
}
