<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $referrals = Referral::where('referrer_id', $user->id)->with('referred:id,display_name,email')->get();

        return response()->json([
            'referral_code' => $user->referral_code,
            'total_referrals' => $referrals->count(),
            'total_earnings' => $referrals->sum('reward_amount'),
            'referrals' => $referrals,
        ]);
    }

    public function generateCode(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->referral_code) {
            $user->update(['referral_code' => strtoupper(\Illuminate\Support\Str::random(8))]);
        }

        return response()->json(['referral_code' => $user->referral_code]);
    }
}
