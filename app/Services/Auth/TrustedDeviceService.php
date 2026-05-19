<?php

namespace App\Services\Auth;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;

class TrustedDeviceService
{
    public function isTrustedDevice(User $user, string $fingerprint): bool
    {
        return TrustedDevice::where('user_id', $user->id)
            ->where('fingerprint', $fingerprint)
            ->where('is_trusted', true)
            ->whereNull('revoked_at')
            ->exists();
    }

    public function registerDevice(User $user, Request $request): TrustedDevice
    {
        $fingerprint = $request->input('fingerprint');

        return TrustedDevice::updateOrCreate(
            ['user_id' => $user->id, 'fingerprint' => $fingerprint],
            [
                'browser' => $request->input('browser'),
                'os' => $request->input('os'),
                'ip_address' => $request->ip(),
                'last_seen_at' => now(),
                'is_trusted' => true,
            ]
        );
    }

    public function revokeDevice(User $user, int $deviceId): bool
    {
        return TrustedDevice::where('user_id', $user->id)
            ->where('id', $deviceId)
            ->update(['revoked_at' => now(), 'is_trusted' => false]) > 0;
    }

    public function getUserDevices(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return TrustedDevice::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->orderByDesc('last_seen_at')
            ->get();
    }

    public function updateLastSeen(User $user, string $fingerprint, Request $request): void
    {
        TrustedDevice::where('user_id', $user->id)
            ->where('fingerprint', $fingerprint)
            ->update([
                'last_seen_at' => now(),
                'ip_address' => $request->ip(),
            ]);
    }
}
