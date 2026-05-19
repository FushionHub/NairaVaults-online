<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TrustedDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(
        protected TrustedDeviceService $trustedDeviceService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'fingerprint' => ['nullable', 'string'],
        ]);

        $cacheKey = 'login_attempts:'.$validated['email'];
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) {
            return response()->json([
                'error' => 'Too many login attempts. Please try again later.',
                'code' => 'RATE_LIMITED',
            ], 429);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            Cache::put($cacheKey, $attempts + 1, 900);

            return response()->json([
                'error' => 'Invalid credentials',
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        Cache::forget($cacheKey);

        if ($user->two_factor_enabled) {
            $fingerprint = $validated['fingerprint'] ?? '';
            $isTrusted = $fingerprint && $this->trustedDeviceService->isTrustedDevice($user, $fingerprint);

            if (! $isTrusted) {
                $tempToken = Cache::put('2fa_pending:'.$user->id, true, 600);

                return response()->json([
                    'requires_2fa' => true,
                    'user_id' => $user->id,
                    'message' => 'Two-factor authentication required',
                ]);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        if (! empty($validated['fingerprint'])) {
            $this->trustedDeviceService->updateLastSeen($user, $validated['fingerprint'], $request);
        }

        return response()->json([
            'user' => $user->only(['id', 'email', 'display_name', 'preferred_currency', 'kyc_status']),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
