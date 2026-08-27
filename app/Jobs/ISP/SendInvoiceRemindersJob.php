<?php

declare(strict_types=1);

namespace App\Jobs\ISP;

use App\Models\Billing\Invoice;
use App\Services\Notifications\WhatsApp\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: Scheduler Job — scan invoice untuk reminder tagihan H-3 / H-1 / H+1.
 *
 * Dijalankan daily oleh Laravel Scheduler (app/Console/Kernel.php).
 * Contoh:
 *   $schedule->job(new SendInvoiceRemindersJob())->dailyAt('08:00');
 */
final class SendInvoiceRemindersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function handle(WhatsAppNotificationService $wa): void
    {
        $today = now()->startOfDay();

        // H+1 (sudah lewat jatuh tempo 1 hari, tapi belum disuspend)
        $overdueH1 = Invoice::query()
            ->whereRaw('(total_amount - paid_amount) > 0')
            ->whereDate('due_date', $today->copy()->subDay()->toDateString())
            ->whereIn('status', ['unpaid', 'partial', 'sent'])
            ->limit(2000)
            ->get();
        foreach ($overdueH1 as $inv) {
            try { $wa->notifyInvoiceReminder($inv, 'h+1'); } catch (\Throwable $e) { Log::warning('[WA-R-H+1] err '.$inv->id.': '.$e->getMessage()); }
        }

        // H-1 (besok jatuh tempo)
        $remH1 = Invoice::query()
            ->whereRaw('(total_amount - paid_amount) > 0')
            ->whereDate('due_date', $today->copy()->addDay()->toDateString())
            ->whereIn('status', ['unpaid', 'partial', 'sent'])
            ->limit(2000)
            ->get();
        foreach ($remH1 as $inv) {
            try { $wa->notifyInvoiceReminder($inv, 'h-1'); } catch (\Throwable $e) { Log::warning('[WA-R-H-1] err '.$inv->id.': '.$e->getMessage()); }
        }

        // H-3 (3 hari lagi jatuh tempo)
        $remH3 = Invoice::query()
            ->whereRaw('(total_amount - paid_amount) > 0')
            ->whereDate('due_date', $today->copy()->addDays(3)->toDateString())
            ->whereIn('status', ['unpaid', 'partial', 'sent'])
            ->limit(2000)
            ->get();
        foreach ($remH3 as $inv) {
            try { $wa->notifyInvoiceReminder($inv, 'h-3'); } catch (\Throwable $e) { Log::warning('[WA-R-H-3] err '.$inv->id.': '.$e->getMessage()); }
        }

        Log::info('[WA-REMINDER] Scan reminder selesai.', [
            'h3_count' => $remH3->count(),
            'h1_count' => $remH1->count(),
            'h_overdue_1' => $overdueH1->count(),
        ]);
    }
}
