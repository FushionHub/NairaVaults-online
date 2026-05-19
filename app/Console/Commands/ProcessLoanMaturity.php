<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class ProcessLoanMaturity extends Command
{
    protected $signature = 'loans:process-maturity';
    protected $description = 'Mark overdue loans';

    public function handle(): int
    {
        Loan::where('status', 'approved')
            ->whereJsonContains('repayment_schedule', [['paid' => false]])
            ->get()
            ->each(function ($loan) {
                $schedule = $loan->repayment_schedule;
                $hasOverdue = collect($schedule)->contains(fn ($item) =>
                    ! $item['paid'] && now()->gt($item['due_date'])
                );
                if ($hasOverdue) {
                    $loan->update(['status' => 'overdue']);
                }
            });

        $this->info('Loan maturity check completed.');

        return Command::SUCCESS;
    }
}
