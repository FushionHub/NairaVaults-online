<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TrustedDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrustedDeviceController extends Controller
{
    public function __construct(
        protected TrustedDeviceService $trustedDeviceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $devices = $this->trustedDeviceService->getUserDevices($request->user());

        return response()->json(['devices' => $devices]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fingerprint' => ['required', 'string'],
            'browser' => ['nullable', 'string'],
            'os' => ['nullable', 'string'],
        ]);

        $device = $this->trustedDeviceService->registerDevice($request->user(), $request);

        return response()->json(['device' => $device], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->trustedDeviceService->revokeDevice($request->user(), $id);

        return response()->json(['message' => 'Device revoked']);
    }
}
