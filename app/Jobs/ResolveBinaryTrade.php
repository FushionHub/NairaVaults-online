<?php

namespace App\Jobs;

use App\Events\TransactionCompleted;
use App\Models\Transaction;
use App\Services\Crypto\BinanceService;
use App\Services\Trading\BinaryTradingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResolveBinaryTrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $transactionId
    ) {}

    public function handle(BinaryTradingService $tradingService, BinanceService $binanceService): void
    {
        $trade = Transaction::findOrFail($this->transactionId);

        if ($trade->status !== 'pending') {
            return;
        }

        $asset = $trade->metadata['asset'] ?? 'BTC';
        $closingPrice = $binanceService->getLivePrice($asset);

        $tradingService->resolveTrade($trade, $closingPrice);

        event(new TransactionCompleted($trade->fresh()));
    }
}
