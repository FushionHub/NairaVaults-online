<?php

namespace App\Http\Controllers\Tier;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TierController extends Controller
{
    public function index(): JsonResponse
    {
        $tiers = Tier::orderBy('sort_order')->get();

        return response()->json(['tiers' => $tiers]);
    }

    public function myTier(Request $request): JsonResponse
    {
        $user = $request->user()->load('tier');

        return response()->json([
            'current_tier' => $user->tier,
            'user_tier_id' => $user->tier_id,
        ]);
    }

    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier_id' => ['required', 'integer', 'exists:tiers,id'],
        ]);

        $user = $request->user();
        $tier = Tier::findOrFail($validated['tier_id']);

        $user->update(['tier_id' => $tier->id]);

        return response()->json([
            'message' => 'Tier upgraded successfully',
            'tier' => $tier,
        ]);
    }
}
