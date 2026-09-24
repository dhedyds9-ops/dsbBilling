<?php

namespace App\Console\Commands;

use App\Models\Customer\CustomerService;
use App\Models\Billing\Invoice;
use Illuminate\Console\Command;

class ReconcileReactivationsCommand extends Command
{
    protected $signature = 'dsbilling:reconcile-reactivations';
    protected $description = 'Re-try failed Customer Service reactivations (Gate 4.5 Safety)';

    public function handle()
    {
        $this->info('Finding failed or stuck pending reactivations...');

        // Cari yang failed atau pending lebih dari 30 menit
        $services = CustomerService::where('status', 'suspended')
            ->where(function ($q) {
                $q->where('reactivation_status', 'failed')
                  ->orWhere(function ($sq) {
                      $sq->where('reactivation_status', 'pending')
                         ->where('updated_at', '<', now()->subMinutes(30));
                  });
            })
            ->get();

        if ($services->isEmpty()) {
            $this->info('No failed reactivations found. All clear.');
            return;
        }

        $this->warn("Found {$services->count()} services to reconcile.");

        foreach ($services as $cs) {
            // Kita butuh trigger job, idealnya via Invoice asli yang sudah terbayar.
            // Ambil invoice terakhir untuk customer ini yang sudah lunas.
            $invoice = Invoice::where('customer_id', $cs->customer_id)
                ->whereIn('status', ['paid', 'partial'])
                ->orderBy('id', 'desc')
                ->first();

            if ($invoice) {
                $this->line("Dispatching reactivation for CustomerService #{$cs->id}");
                
                // Ubah status jadi pending lagi agar diambil oleh job
                $cs->update(['reactivation_status' => 'pending']);
                
                \App\Jobs\Billing\ReactivateCustomerJob::dispatch($invoice);
            } else {
                $this->error("Cannot reconcile CS #{$cs->id} - No paid invoice found.");
            }
        }

        $this->info('Reconciliation dispatched to Queue.');
    }
}
