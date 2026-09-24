<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\Invoice;
use App\Models\Customer\CustomerService;
use App\Services\ISP\ISPProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoSuspend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:auto-suspend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses isolir otomatis untuk tagihan jatuh tempo (Engine Isolir)';

    /**
     * Execute the console command.
     */
    public function handle(ISPProvisioningService $provisioningService)
    {
        $this->info('Memulai pengecekan tagihan jatuh tempo...');
        Log::info('Cron: Memulai proses auto-suspend.');

        // Cari invoice yang belum lunas, dan sudah lewat jatuh tempo.
        $invoices = Invoice::with(['customer'])
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->get();

        $suspendedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($invoices as $invoice) {
            // Cek Grace Period (Janji Bayar)
            if ($invoice->hasActiveGracePeriod()) {
                $this->line("Melewati Invoice #{$invoice->invoice_number} - Dalam masa Janji Bayar hingga {$invoice->grace_period_until}");
                $skippedCount++;
                continue;
            }

            $customerServices = CustomerService::where('customer_id', $invoice->customer_id)
                ->where('status', 'active')
                ->get();

            if ($customerServices->isEmpty()) {
                $this->line("Melewati Invoice #{$invoice->invoice_number} - Tidak ada layanan aktif (mungkin sudah disuspend).");
                $skippedCount++;
                continue;
            }

            foreach ($customerServices as $service) {
                try {
                    $this->info("Menangguhkan (Isolir) layanan ID #{$service->id} untuk Invoice #{$invoice->invoice_number}...");
                    
                    $result = $provisioningService->suspendCustomerService($service);

                    if ($result['success']) {
                        $this->info("Sukses mematikan layanan.");
                        $suspendedCount++;
                    } else {
                        $this->error("Gagal menangguhkan layanan #{$service->id}. Error: " . implode(' | ', $result['errors']));
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error fatal saat mematikan layanan #{$service->id}: " . $e->getMessage());
                    $failedCount++;
                }
            }
            
            // Ubah status invoice jika perlu (contoh: jadi overdue)
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }
        }

        $this->info('Proses Auto-Suspend selesai.');
        $this->info("Total diisolir: {$suspendedCount}, Dilewati: {$skippedCount}, Gagal: {$failedCount}");
        
        Log::info('Cron: Proses auto-suspend selesai.', [
            'suspended' => $suspendedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
        ]);
    }
}
