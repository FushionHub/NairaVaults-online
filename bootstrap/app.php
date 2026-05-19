<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'kyc.verified' => \App\Http\Middleware\KycVerified::class,
            '2fa.verified' => \App\Http\Middleware\TwoFactorVerified::class,
            'trusted.device' => \App\Http\Middleware\TrustedDevice::class,
            'idempotency' => \App\Http\Middleware\IdempotencyKey::class,
            'account.type' => \App\Http\Middleware\AccountType::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') && ! config('app.debug')) {
                return response()->json([
                    'error' => 'Internal Server Error',
                    'code' => 'SERVER_ERROR',
                    'requestId' => $request->header('X-Request-ID', \Illuminate\Support\Str::uuid()->toString()),
                ], 500);
            }
        });
    })->create();
