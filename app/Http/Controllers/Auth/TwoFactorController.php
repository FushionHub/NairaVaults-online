<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TwoFactorController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {}

    public function setup(Request $request): JsonResponse
    {
        $user = $request->user();
        $secret = $this->twoFactorService->generateSecret();
        $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCode = base64_encode($writer->writeString($qrCodeUrl));

        Cache::put('2fa_setup:'.$user->id, $secret, 600);

        return response()->json([
            'secret' => $secret,
            'qr_code' => 'data:image/svg+xml;base64,'.$qrCode,
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $secret = Cache::get('2fa_setup:'.$user->id);

        if (! $secret) {
            return response()->json(['error' => 'Setup session expired'], 400);
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA;
        if (! $google2fa->verifyKey($secret, $validated['code'])) {
            return response()->json(['error' => 'Invalid verification code'], 422);
        }

        $recoveryCodes = $this->twoFactorService->enableTwoFactor($user, $secret);
        Cache::forget('2fa_setup:'.$user->id);

        return response()->json([
            'message' => 'Two-factor authentication enabled',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
            'fingerprint' => ['nullable', 'string'],
        ]);

        $lockoutKey = '2fa_lockout:'.$validated['user_id'];
        if (Cache::get($lockoutKey, 0) >= 3) {
            return response()->json([
                'error' => 'Account temporarily locked. Try again in 15 minutes.',
                'code' => '2FA_LOCKED',
            ], 429);
        }

        $user = \App\Models\User::findOrFail($validated['user_id']);

        $verified = $this->twoFactorService->verifyCode($user, $validated['code'])
            || $this->twoFactorService->verifyRecoveryCode($user, $validated['code']);

        if (! $verified) {
            Cache::increment($lockoutKey);
            Cache::put($lockoutKey, Cache::get($lockoutKey, 0), 900);

            return response()->json(['error' => 'Invalid code'], 422);
        }

        Cache::forget($lockoutKey);
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'email', 'display_name', 'preferred_currency', 'kyc_status']),
            'token' => $token,
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $this->twoFactorService->verifyCode($user, $validated['code'])) {
            return response()->json(['error' => 'Invalid verification code'], 422);
        }

        $this->twoFactorService->disableTwoFactor($user);

        return response()->json(['message' => 'Two-factor authentication disabled']);
    }
}
