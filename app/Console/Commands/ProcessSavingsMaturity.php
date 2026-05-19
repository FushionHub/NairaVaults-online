<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMaturityPayout;
use Illuminate\Console\Command;

class ProcessSavingsMaturity extends Command
{
    protected $signature = 'savings:process-maturity';
    protected $description = 'Process matured savings plans and credit interest';

    public function handle(): int
    {
        ProcessMaturityPayout::dispatch();
        $this->info('Savings maturity processing dispatched.');

        return Command::SUCCESS;
    }
}
