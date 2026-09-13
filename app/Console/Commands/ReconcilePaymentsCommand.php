<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Adapters\Payment\PaymentOrchestrationService;

class ReconcilePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile pending payments by checking gateway status directly';

    /**
     * Execute the console command.
     */
    public function handle(PaymentOrchestrationService $orchestration): int
    {
        $this->info('Starting payment reconciliation...');
        $processed = $orchestration->reconcilePendingPayments();
        $this->info("Reconciliation completed. Processed: {$processed} payments.");
        
        return 0;
    }
}
