<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['notificationPreference', 'tier']);

        return response()->json(['user' => $user]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'unique:users,phone,' . $request->user()->id],
            'preferred_currency' => ['nullable', 'string', 'in:NGN,USD,EUR,GBP,GHS'],
        ]);

        $request->user()->update(array_filter($validated));

        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password changed']);
    }

    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate(['photo' => ['required', 'image', 'max:5120']]);

        $path = $request->file('photo')->store('profile-photos', 'public');
        $request->user()->update(['profile_photo' => $path]);

        return response()->json(['photo' => $path]);
    }
}
