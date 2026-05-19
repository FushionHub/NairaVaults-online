<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KycVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->kyc_status !== 'verified') {
            return response()->json([
                'error' => 'KYC verification required',
                'code' => 'KYC_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
