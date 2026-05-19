<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if ($idempotencyKey) {
            $cacheKey = 'idempotency:' . $idempotencyKey;

            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $response = $next($request);

            Cache::put($cacheKey, $response->getContent(), 86400);

            return $response;
        }

        return $next($request);
    }
}
