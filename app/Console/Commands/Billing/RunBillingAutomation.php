<?php

namespace App\Console\Commands\Billing;

use App\Jobs\Billing\GenerateInvoiceJob;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use App\Services\ISP\ISPProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * billing:run-automation
 *
 * Pipeline otomasi billing harian ISP:
 *   1. Regenerate invoice untuk subscription yang sudah jatuh tempo (jika belum ada invoice bulan ini)
 *   2. Kirim notifikasi jatuh tempo H-3
 *   3. Tandai invoice > due_date sebagai overdue
 *   4. Isolir (suspend) pelanggan yang invoicenya overdue dan tidak dalam grace period
 *
 * Dijadwalkan setiap hari pukul 18:00 WIB (setelah jam kerja).
 */
class RunBillingAutomation extends Command
{
    protected $signature = 'billing:run-automation
                            {--dry-run : Jalankan tanpa melakukan perubahan nyata}
                            {--skip-suspend : Skip proses isolir otomatis}
                            {--skip-invoice : Skip proses generate invoice}';

    protected $description = 'Pipeline otomasi billing harian ISP: generate invoice, overdue marking, isolir otomatis';

    public function handle(ISPProvisioningService $provisioning): int
    {
        $dryRun       = (bool) $this->option('dry-run');
        $skipSuspend  = (bool) $this->option('skip-suspend');
        $skipInvoice  = (bool) $this->option('skip-invoice');

        $this->info('=== billing:run-automation dimulai (' . now()->format('Y-m-d H:i:s') . ') ===');
        $this->info($dryRun ? '[DRY-RUN] Tidak ada perubahan yang akan disimpan.' : '[LIVE] Mode produksi.');
        Log::info('billing:run-automation start', ['dry_run' => $dryRun]);

        $stats = [
            'invoice_queued'  => 0,
            'overdue_marked'  => 0,
            'suspended'       => 0,
            'skipped_grace'   => 0,
            'failed'          => 0,
        ];

        // ─── STEP 1: Generate invoice untuk subscription jatuh tempo ────────────
        if (!$skipInvoice) {
            $this->line('');
            $this->info('[1/3] Memeriksa subscription yang harus ditagih...');

            try {
                Subscription::where('status', 'active')
                    ->where('next_billing_date', '<=', now())
                    ->chunkById(100, function ($subs) use (&$stats, $dryRun) {
                        foreach ($subs as $sub) {
                            try {
                                if (!$dryRun) {
                                    GenerateInvoiceJob::dispatch($sub);
                                }
                                $stats['invoice_queued']++;
                                $this->line("  → Subscription #{$sub->id} ({$sub->customer_service_id}) dijadwalkan.");
                            } catch (Throwable $e) {
                                $stats['failed']++;
                                $this->error("  ✗ Gagal dispatch subscription #{$sub->id}: " . $e->getMessage());
                                Log::error('billing:run-automation invoice dispatch failed', [
                                    'sub_id' => $sub->id,
                                    'err'    => $e->getMessage(),
                                ]);
                            }
                        }
                    });

                $this->info("  Selesai: {$stats['invoice_queued']} invoice masuk antrian.");
            } catch (Throwable $e) {
                $this->error('  FATAL saat proses invoice: ' . $e->getMessage());
                Log::error('billing:run-automation invoice step failed', ['err' => $e->getMessage()]);
            }
        }

        // ─── STEP 2: Tandai invoice jatuh tempo sebagai overdue ─────────────────
        $this->line('');
        $this->info('[2/3] Menandai invoice overdue...');

        try {
            $overdueQuery = Invoice::where('status', 'unpaid')
                ->where('due_date', '<', now()->startOfDay());

            $overdueCount = $overdueQuery->count();
            $this->line("  Ditemukan: {$overdueCount} invoice unpaid melewati due_date.");

            if (!$dryRun && $overdueCount > 0) {
                $updated = Invoice::where('status', 'unpaid')
                    ->where('due_date', '<', now()->startOfDay())
                    ->update(['status' => 'overdue', 'updated_at' => now()]);
                $stats['overdue_marked'] = $updated;
                $this->info("  ✓ {$updated} invoice ditandai overdue.");
            } else {
                $stats['overdue_marked'] = $overdueCount;
            }
        } catch (Throwable $e) {
            $this->error('  FATAL saat marking overdue: ' . $e->getMessage());
            Log::error('billing:run-automation overdue step failed', ['err' => $e->getMessage()]);
        }

        // ─── STEP 3: Isolir (suspend) pelanggan overdue ──────────────────────────
        if (!$skipSuspend) {
            $this->line('');
            $this->info('[3/3] Memproses isolir otomatis pelanggan overdue...');

            try {
                Invoice::with(['customer'])
                    ->whereIn('status', ['overdue'])
                    ->where('due_date', '<', now())
                    ->chunkById(50, function ($invoices) use ($provisioning, &$stats, $dryRun) {
                        foreach ($invoices as $invoice) {
                            // Skip jika masih dalam grace period / janji bayar
                            if (method_exists($invoice, 'hasActiveGracePeriod') && $invoice->hasActiveGracePeriod()) {
                                $stats['skipped_grace']++;
                                $this->line("  ⏭ Dilewati (grace period): Invoice #{$invoice->invoice_number}");
                                continue;
                            }

                            $services = CustomerService::where('customer_id', $invoice->customer_id)
                                ->where('status', 'active')
                                ->get();

                            if ($services->isEmpty()) {
                                $stats['skipped_grace']++;
                                continue;
                            }

                            foreach ($services as $service) {
                                try {
                                    if (!$dryRun) {
                                        $result = $provisioning->suspendCustomerService($service);
                                        if ($result['success'] ?? false) {
                                            $stats['suspended']++;
                                            $this->line("  ✓ Layanan #{$service->id} customer #{$invoice->customer_id} diisolir.");
                                        } else {
                                            $stats['failed']++;
                                            $this->warn("  ✗ Gagal isolir layanan #{$service->id}");
                                        }
                                    } else {
                                        $stats['suspended']++;
                                        $this->line("  [DRY] Akan isolir layanan #{$service->id} (Invoice #{$invoice->invoice_number})");
                                    }
                                } catch (Throwable $e) {
                                    $stats['failed']++;
                                    Log::error('billing:run-automation suspend failed', [
                                        'service_id' => $service->id,
                                        'err'        => $e->getMessage(),
                                    ]);
                                }
                            }
                        }
                    });

                $this->info("  Selesai: {$stats['suspended']} layanan diisolir, {$stats['skipped_grace']} dilewati (grace period).");
            } catch (Throwable $e) {
                $this->error('  FATAL saat proses isolir: ' . $e->getMessage());
                Log::error('billing:run-automation suspend step failed', ['err' => $e->getMessage()]);
            }
        }

        // ─── RINGKASAN ─────────────────────────────────────────────────────────
        $this->line('');
        $this->info('=== RINGKASAN ===');
        $this->table(
            ['Proses', 'Jumlah'],
            [
                ['Invoice dijadwalkan ke antrian', $stats['invoice_queued']],
                ['Invoice ditandai overdue',        $stats['overdue_marked']],
                ['Layanan diisolir',                $stats['suspended']],
                ['Dilewati (grace period)',          $stats['skipped_grace']],
                ['Gagal',                           $stats['failed']],
            ]
        );

        Log::info('billing:run-automation selesai', $stats);
        $this->info('billing:run-automation selesai pada ' . now()->format('Y-m-d H:i:s'));

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
