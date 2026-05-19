<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesWebhookSignatures
{
    protected function verifyHmacSha512(Request $request, string $secret, string $headerName): bool
    {
        $signature = $request->header($headerName);

        if (empty($signature)) {
            return false;
        }

        $computedHash = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($computedHash, $signature);
    }

    protected function verifyHmacSha256(Request $request, string $secret, string $headerName): bool
    {
        $signature = $request->header($headerName);

        if (empty($signature)) {
            return false;
        }

        $computedHash = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($computedHash, $signature);
    }
}
