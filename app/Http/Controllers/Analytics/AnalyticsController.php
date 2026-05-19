<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use App\Models\FiatAccount;
use App\Models\PortfolioSnapshot;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        $fiatTotal = FiatAccount::where('user_id', $user->id)->sum('balance');
        $cryptoTotal = CryptoWallet::select(CryptoWallet::safeSelect())
            ->where('user_id', $user->id)->sum('balance');

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'fiat_total' => (string) $fiatTotal,
            'crypto_total' => (string) $cryptoTotal,
            'total_value' => bcadd((string) $fiatTotal, (string) $cryptoTotal, 8),
            'recent_transactions' => $recentTransactions,
        ]);
    }

    public function performance(Request $request): JsonResponse
    {
        $snapshots = PortfolioSnapshot::where('user_id', $request->user()->id)
            ->orderByDesc('snapshot_date')
            ->limit(30)
            ->get();

        return response()->json(['snapshots' => $snapshots]);
    }
}
