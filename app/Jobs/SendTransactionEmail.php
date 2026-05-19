<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTransactionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $transactionId
    ) {}

    public function handle(): void
    {
        $transaction = Transaction::with('user')->findOrFail($this->transactionId);

        Mail::raw(
            "Transaction {$transaction->reference} - {$transaction->type} of {$transaction->amount} {$transaction->currency} is {$transaction->status}.",
            function ($message) use ($transaction) {
                $message->to($transaction->user->email)
                    ->subject("NairaVault: {$transaction->type} - {$transaction->status}");
            }
        );
    }
}
