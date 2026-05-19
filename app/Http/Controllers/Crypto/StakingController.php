<?php

namespace App\Http\Controllers\Crypto;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use App\Models\StakingPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StakingController extends Controller
{
    private array $stakingPools = [
        ['coin_symbol' => 'ETH', 'apy' => '4.50', 'min_stake' => '0.01'],
        ['coin_symbol' => 'BNB', 'apy' => '5.20', 'min_stake' => '0.1'],
        ['coin_symbol' => 'SOL', 'apy' => '6.80', 'min_stake' => '1'],
        ['coin_symbol' => 'MATIC', 'apy' => '7.50', 'min_stake' => '10'],
        ['coin_symbol' => 'DOT', 'apy' => '12.00', 'min_stake' => '5'],
    ];

    public function index(Request $request): JsonResponse
    {
        $positions = StakingPosition::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['positions' => $positions, 'pools' => $this->stakingPools]);
    }

    public function stake(Request $request): JsonResponse
    {
        $request->validate([
            'coin_symbol' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.00000001',
            'lock_days' => 'required|integer|in:30,60,90,180,365',
        ]);

        $user = $request->user();
        $wallet = CryptoWallet::where('user_id', $user->id)
            ->where('coin_symbol', strtoupper($request->coin_symbol))
            ->firstOrFail();

        if (! $wallet->hasBalance((string) $request->amount)) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $pool = collect($this->stakingPools)->firstWhere('coin_symbol', strtoupper($request->coin_symbol));
        $apy = $pool['apy'] ?? '5.00';

        $wallet->subtractBalance((string) $request->amount);

        $position = StakingPosition::create([
            'user_id' => $user->id,
            'coin_symbol' => strtoupper($request->coin_symbol),
            'amount' => (string) $request->amount,
            'apy' => $apy,
            'start_date' => now(),
            'end_date' => now()->addDays($request->lock_days),
            'rewards_earned' => '0.00000000',
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Staking position created', 'position' => $position], 201);
    }

    public function unstake(int $id, Request $request): JsonResponse
    {
        $position = StakingPosition::where('user_id', $request->user()->id)->findOrFail($id);

        if ($position->status !== 'active') {
            return response()->json(['error' => 'Position is not active'], 400);
        }

        $wallet = CryptoWallet::where('user_id', $request->user()->id)
            ->where('coin_symbol', $position->coin_symbol)
            ->firstOrFail();

        $totalReturn = bcadd($position->amount, $position->rewards_earned, 8);

        if (now()->lt($position->end_date)) {
            $penalty = bcmul($totalReturn, '0.10', 8);
            $totalReturn = bcsub($totalReturn, $penalty, 8);
        }

        $wallet->addBalance($totalReturn);
        $position->update(['status' => 'unstaked']);

        return response()->json(['message' => 'Unstaked successfully', 'returned' => $totalReturn]);
    }
}
