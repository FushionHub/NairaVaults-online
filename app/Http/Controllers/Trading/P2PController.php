<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\P2pOffer;
use App\Services\Trading\P2PEscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class P2PController extends Controller
{
    public function __construct(
        protected P2PEscrowService $escrowService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $offers = P2pOffer::where('status', 'open')
            ->when($request->query('direction'), fn ($q, $dir) => $q->where('direction', $dir))
            ->when($request->query('coin'), fn ($q, $coin) => $q->where('coin_symbol', $coin))
            ->with('creator:id,display_name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($offers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coin_symbol' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'rate_per_unit' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'in:NGN,USD'],
            'direction' => ['required', 'string', 'in:buy,sell'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $offer = $this->escrowService->createOffer($request->user(), $validated);

        return response()->json(['offer' => $offer], 201);
    }

    public function confirm(int $offerId, Request $request): JsonResponse
    {
        $offer = P2pOffer::findOrFail($offerId);

        $this->escrowService->confirmTrade($offer, $request->user());

        return response()->json(['message' => 'Trade confirmed', 'offer' => $offer->fresh()]);
    }

    public function dispute(int $offerId): JsonResponse
    {
        $offer = P2pOffer::findOrFail($offerId);

        $this->escrowService->disputeTrade($offer);

        return response()->json(['message' => 'Dispute raised', 'offer' => $offer->fresh()]);
    }

    public function destroy(int $offerId, Request $request): JsonResponse
    {
        $offer = P2pOffer::where('creator_user_id', $request->user()->id)
            ->where('status', 'open')
            ->findOrFail($offerId);

        $this->escrowService->cancelOffer($offer);

        return response()->json(['message' => 'Offer cancelled']);
    }
}
