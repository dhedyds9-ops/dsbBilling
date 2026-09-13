<?php

namespace App\Jobs\ISP;

use App\Enums\ISP\CoaType;
use App\Jobs\ISP\Radius\DispatchBatchCoaJob;
use App\Models\Billing\Invoice;
use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Services\ISP\PPPoEService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOverdueInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public readonly int $graceDays = 0,
    ) {}

    public function handle(PPPoEService $pppoeService): void
    {
        Log::info('[CheckOverdueInvoicesJob] Starting overdue invoice check with batch COA...');

        // Get ALL overdue invoices (ignoring global graceDays first)
        $overdueInvoices = Invoice::query()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->with(['customer.customerServices'])
            ->get();

        Log::info("[CheckOverdueInvoicesJob] Found {$overdueInvoices->count()} overdue invoices (raw)");

        if ($overdueInvoices->count() === 0) {
            Log::info('[CheckOverdueInvoicesJob] Nothing to process. Exiting.');
            return;
        }

        // 1. Kumpulkan semua CustomerService yang status=active dari invoice overdue
        /** @var array<int,CustomerService> $activeServicesById */
        $activeServicesById = [];
        foreach ($overdueInvoices as $invoice) {
            $customer = $invoice->customer;
            if (!$customer) continue;
            
            foreach ($customer->customerServices as $cs) {
                $status = is_string($cs->status) ? $cs->status : (string)($cs->status?->value ?? '');
                
                // Get custom grace period from service attributes, fallback to global $this->graceDays
                $customGraceDays = $cs->attributes['grace_period_days'] ?? $this->graceDays;
                
                // Check if this specific invoice has passed its custom grace period
                $isPassedGracePeriod = \Carbon\Carbon::parse($invoice->due_date)->addDays($customGraceDays)->isPast();
                
                if ($status === 'active' && $isPassedGracePeriod) {
                    $activeServicesById[(int)$cs->id] = $cs;
                }
            }
        }

        Log::info("[CheckOverdueInvoicesJob] Found " . count($activeServicesById) . " active customer services to suspend");

        if (count($activeServicesById) === 0) {
            Log::info('[CheckOverdueInvoicesJob] No active services to suspend. Exiting.');
            return;
        }

        // 2. Ambil semua PPPoEUser yang terkait dengan customer services tsb (status active)
        $serviceIds = array_keys($activeServicesById);
        $pppoeUsers = PPPoEUser::query()
            ->whereIn('customer_service_id', $serviceIds)
            ->where('status', 'active')
            ->with(['customerService'])
            ->get();

        Log::info("[CheckOverdueInvoicesJob] Found {$pppoeUsers->count()} active PPPoE users for batch suspend COA");

        // 3. Batch update status customer_services + pppoe_users menjadi suspended via DB transaction
        //    + fire events (tapi COA dikirim via batch queue, bukan sync)
        $suspendedCount = 0;
        try {
            DB::beginTransaction();

            foreach ($pppoeUsers as $pu) {
                try {
                    // Status update melalui service untuk memastikan Event ter-fire (listener bisa hook untuk workflow lain)
                    // TAPI: skip pemanggilan COA SYNC di PPPoEService, karena kita dispatch batch setelah ini
                    // Jadi panggil method yang hanya ubah status DB tanpa network call
                    if (method_exists($pppoeService, 'suspendStatusOnly')) {
                        $pppoeService->suspendStatusOnly($pu->id, 1);
                    } else {
                        // Fallback: langsung update DB kalau method status-only tidak ada
                        $pu->status = 'suspended';
                        $pu->saveQuietly();
                        $cs = $pu->customerService;
                        if ($cs && $cs->status !== 'suspended') {
                            $cs->status = 'suspended';
                            $cs->saveQuietly();
                        }
                    }
                    $suspendedCount++;
                } catch (\Throwable $e) {
                    Log::warning("[CheckOverdueInvoicesJob] Failed to update status PPPoEUser#{$pu->id}", ['err' => $e->getMessage()]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::critical('[CheckOverdueInvoicesJob] DB transaction failed, aborting batch COA', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        Log::info("[CheckOverdueInvoicesJob] Successfully updated {$suspendedCount} PPPoE user statuses (DB)");

        // 4. DISPATCH BATCH COA: semua user yang sudah di-update statusnya
        //    Network enforcement (rate-limit/disconnect) dilakukan async oleh Horizon
        if ($pppoeUsers->count() > 0) {
            try {
                DispatchBatchCoaJob::dispatch(
                    coaType: CoaType::Suspend,
                    users: $pppoeUsers,
                    operatorId: 1,
                    batchName: "Overdue-Suspend-" . now()->format('Ymd-Hi') . "-{$pppoeUsers->count()}u",
                )->onQueue('radius-coa');

                Log::info("[CheckOverdueInvoicesJob] Dispatched batch COA Suspend for {$pppoeUsers->count()} PPPoE users to queue");
            } catch (\Throwable $e) {
                Log::critical('[CheckOverdueInvoicesJob] Failed to dispatch DispatchBatchCoaJob', [
                    'err' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        Log::info('[CheckOverdueInvoicesJob] Overdue invoice check completed. Batch COA queued for Horizon workers.');
    }
}
