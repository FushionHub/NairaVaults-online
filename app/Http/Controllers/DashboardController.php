<?php

namespace App\Http\Controllers;

use App\Models\CryptoWallet;
use App\Models\FiatAccount;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $fiatAccounts = FiatAccount::where('user_id', $user->id)->get();
        $cryptoWallets = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)->get();

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'user' => $user->only(['id', 'email', 'display_name', 'kyc_status', 'preferred_currency', 'referral_code']),
            'fiat_accounts' => $fiatAccounts,
            'crypto_wallets' => $cryptoWallets,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
