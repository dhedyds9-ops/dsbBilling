<?php

namespace App\Console\Commands\Billing;

use App\Jobs\Billing\GenerateInvoiceJob;
use App\Models\Billing\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MassGenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:mass-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mass generate invoices for due subscriptions safely using chunking and jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai Mass Billing Generator (Anniversary Mode)...');
        Log::info('Cron: Memulai proses mass billing.');

        $count = 0;

        // Process in chunks of 100 to avoid memory limit issues
        Subscription::where('status', 'active')
            ->where('next_billing_date', '<=', now())
            ->chunkById(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    // Dispatch to queue to avoid timeout during massive processing
                    GenerateInvoiceJob::dispatch($subscription);
                    $count++;
                }
            });

        $this->info("Berhasil memasukkan {$count} tagihan ke dalam antrean (Queue).");
        Log::info('Cron: Proses mass billing selesai.', ['count' => $count]);
    }
}
