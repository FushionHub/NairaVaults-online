<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\PriceAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceAlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alerts = PriceAlert::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['alerts' => $alerts]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'target_price' => 'required|numeric|min:0.01',
            'condition' => 'required|in:above,below',
        ]);

        $alert = PriceAlert::create([
            'user_id' => $request->user()->id,
            'coin_symbol' => strtoupper($request->coin_symbol),
            'target_price' => (string) $request->target_price,
            'condition' => $request->condition,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Price alert created', 'alert' => $alert], 201);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        PriceAlert::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Alert deleted']);
    }
}
