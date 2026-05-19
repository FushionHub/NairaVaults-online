<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && ! session('2fa_verified')) {
            return response()->json([
                'error' => 'Two-factor authentication required',
                'code' => '2FA_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
