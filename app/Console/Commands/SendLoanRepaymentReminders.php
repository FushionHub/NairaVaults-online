<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;

class SendLoanRepaymentReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Send repayment reminders 3 days before due date';

    public function handle(NotificationService $notificationService): int
    {
        $loans = Loan::where('status', 'approved')->with('user')->get();

        foreach ($loans as $loan) {
            $schedule = $loan->repayment_schedule ?? [];

            foreach ($schedule as $payment) {
                if (! $payment['paid'] && now()->addDays(3)->gte($payment['due_date']) && now()->lt($payment['due_date'])) {
                    $notificationService->send(
                        $loan->user,
                        'loan_reminder',
                        'Loan Repayment Due Soon',
                        "Your loan repayment of {$payment['amount']} {$loan->currency} is due on {$payment['due_date']}."
                    );
                }
            }
        }

        $this->info('Loan reminders sent.');

        return Command::SUCCESS;
    }
}
