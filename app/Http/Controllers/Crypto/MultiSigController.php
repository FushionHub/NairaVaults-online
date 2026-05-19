<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\MultiSigWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultiSigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wallets = MultiSigWallet::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['wallets' => $wallets]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'required_signatures' => 'required|integer|min:2',
            'signatories' => 'required|array|min:2',
            'signatories.*' => 'required|string',
        ]);

        if ($request->required_signatures > count($request->signatories)) {
            return response()->json(['error' => 'Required signatures cannot exceed number of signatories'], 400);
        }

        $wallet = MultiSigWallet::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'wallet_address' => '0x'.bin2hex(random_bytes(20)),
            'required_signatures' => $request->required_signatures,
            'signatories' => $request->signatories,
            'pending_transactions' => [],
        ]);

        return response()->json(['message' => 'Multi-sig wallet created', 'wallet' => $wallet], 201);
    }

    public function proposeTransaction(int $walletId, Request $request): JsonResponse
    {
        $wallet = MultiSigWallet::where('user_id', $request->user()->id)->findOrFail($walletId);

        $request->validate([
            'to_address' => 'required|string',
            'amount' => 'required|numeric|min:0.00000001',
            'coin_symbol' => 'required|string|max:20',
        ]);

        $pending = $wallet->pending_transactions ?? [];
        $pending[] = [
            'id' => count($pending) + 1,
            'to_address' => $request->to_address,
            'amount' => (string) $request->amount,
            'coin_symbol' => $request->coin_symbol,
            'signatures' => [$request->user()->email],
            'status' => 'pending',
            'created_at' => now()->toISOString(),
        ];

        $wallet->update(['pending_transactions' => $pending]);

        return response()->json(['message' => 'Transaction proposed']);
    }
}
