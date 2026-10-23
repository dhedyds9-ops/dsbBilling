<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\Billing\BaseBillingComponent;
use App\Models\Payment\Payment;
use App\Models\CRM\Customer;

class Index extends BaseBillingComponent
{
    public $selectAll = false;
    public $selected = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $allIds = $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->selectAll = count($this->selected) > 0 && count(array_diff($allIds, $this->selected)) === 0;
    }

    public function syncPayment($id)
    {
        try {
            $payment = Payment::with('invoices')->findOrFail($id);

            if ($payment->status === 'success') {
                $this->dispatch('swal:alert', [
                    'icon' => 'info',
                    'title' => 'Sudah Lunas',
                    'text' => 'Pembayaran ini sudah berstatus Sukses.'
                ]);
                return;
            }

            $gatewayKey = $payment->gateway ?: 'midtrans';
            $registry = app(\App\Services\Adapters\Payment\PaymentGatewayRegistry::class);
            $driver = $registry->get($gatewayKey);

            if (!$driver || !method_exists($driver, 'checkStatus')) {
                $this->dispatch('swal:alert', [
                    'icon' => 'warning',
                    'title' => 'Driver Tidak Didukung',
                    'text' => "Driver [{$gatewayKey}] tidak mendukung pengecekan status otomatis."
                ]);
                return;
            }

            $checkResp = $driver->checkStatus($payment->reference_number);

            if (in_array($checkResp->status, ['paid', 'success', 'settlement', 'capture'])) {
                $syntheticEvent = new \App\Services\Adapters\Payment\ValueObjects\WebhookEvent(
                    eventType: 'payment.paid',
                    gatewayOrderId: $checkResp->gatewayReferenceId ?: $payment->reference_number,
                    merchantOrderId: $payment->reference_number,
                    amountIdr: $checkResp->paidAmountIdr ?: (int)$payment->amount,
                    paidAmountIdr: $checkResp->paidAmountIdr ?: (int)$payment->amount,
                    paymentMethod: $checkResp->paymentMethod ?: $payment->method,
                    paidAtIso: $checkResp->paidAtIso ?: now()->toISOString(),
                    signatureVerifiedBy: 'admin_manual_sync',
                    rawBodyHash: hash('sha256', 'manual_sync_' . $payment->reference_number . '_' . time()),
                    rawPayload: ['synthetic' => true, 'source' => 'admin_payment_index', 'status' => $checkResp->status],
                );

                app(\App\Services\Adapters\Payment\PaymentOrchestrationService::class)->handleWebhookPaid($syntheticEvent, $gatewayKey);

                $this->dispatch('swal:alert', [
                    'icon' => 'success',
                    'title' => 'Pembayaran Sukses!',
                    'text' => "Pembayaran {$payment->reference_number} telah terverifikasi lunas dari {$gatewayKey}."
                ]);
            } elseif ($checkResp->status === 'expired') {
                $payment->update(['status' => 'expired']);
                $this->dispatch('swal:alert', [
                    'icon' => 'warning',
                    'title' => 'Kedaluwarsa',
                    'text' => "Transaksi di {$gatewayKey} telah kedaluwarsa (expired)."
                ]);
            } else {
                $this->dispatch('swal:alert', [
                    'icon' => 'info',
                    'title' => 'Belum Dibayar',
                    'text' => "Status di {$gatewayKey}: " . ($checkResp->status ?? 'pending') . ". Transaksi belum diselesaikan pelanggan."
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error syncing payment: ' . $e->getMessage());
            $this->dispatch('swal:alert', [
                'icon' => 'error',
                'title' => 'Gagal Sinkronisasi',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function syncAllPending()
    {
        try {
            $count = app(\App\Services\Adapters\Payment\PaymentOrchestrationService::class)->reconcilePendingPayments();
            $this->dispatch('swal:alert', [
                'icon' => 'success',
                'title' => 'Sinkronisasi Selesai',
                'text' => "Berhasil memverifikasi {$count} pembayaran pending dari payment gateway."
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:alert', [
                'icon' => 'error',
                'title' => 'Gagal Sinkronisasi',
                'text' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        $payment = Payment::withoutGlobalScopes()->with('invoices')->findOrFail($id);
        $this->authorize('delete', $payment);
        
        // Reverse invoice paid_amount
        foreach ($payment->invoices as $invoice) {
            $invoice->paid_amount = max(0, $invoice->paid_amount - $payment->amount);
            $invoice->status = $invoice->paid_amount <= 0 ? 'unpaid' : ($invoice->paid_amount < $invoice->total_amount ? 'partial' : 'paid');
            $invoice->save();
        }
        
        $payment->delete();
        session()->flash('success', 'Payment berhasil dihapus dan tagihan dikembalikan ke belum dibayar!');
    }

    public function bulkDelete()
    {
        if (empty($this->selected)) return;

        $payments = Payment::withoutGlobalScopes()->with('invoices')->whereIn('id', array_map('intval', $this->selected))->get();
        $count = 0;
        
        foreach ($payments as $payment) {
            try {
                $this->authorize('delete', $payment);
            } catch (\Throwable) {
                continue;
            }
            // Reverse invoice paid_amount
            foreach ($payment->invoices as $invoice) {
                $invoice->paid_amount = max(0, $invoice->paid_amount - $payment->amount);
                $invoice->status = $invoice->paid_amount <= 0 ? 'unpaid' : ($invoice->paid_amount < $invoice->total_amount ? 'partial' : 'paid');
                $invoice->save();
            }
            $payment->delete();
            $count++;
        }

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('success', "$count Pembayaran berhasil dihapus dan tagihan terkait telah diperbarui!");
    }

    public function getIsResellerProperty(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('reseller') || !empty($user->getEffectiveResellerId()));
    }

    public function getEffectiveResellerIdProperty(): ?int
    {
        $user = auth()->user();
        return $user ? ($user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null)) : null;
    }

    public function mount()
    {
        parent::mount();
        $this->authorize('viewAny', Payment::class);

        $reqFilters = request('filters', []);
        $this->filters = [
            'status' => $reqFilters['status'] ?? '',
            'method' => $reqFilters['method'] ?? ($reqFilters['payment_method'] ?? ''),
            'reseller_id' => $reqFilters['reseller_id'] ?? 'all',
        ];

        if ($this->isReseller) {
            $this->activeModule = 'reseller-portal';
            $this->activePage = 'reseller-portal.billing.payments';
            $this->breadcrumbs = [
                ['label' => 'Portal Reseller', 'url' => route('reseller-portal.dashboard')],
                ['label' => 'Data Tagihan', 'url' => route('reseller-portal.billing.payments')],
                ['label' => 'Pembayaran'],
            ];
            $this->filters['reseller_id'] = (string) $this->effectiveResellerId;
        } else {
            $this->activeModule = 'billing';
            $this->activePage = 'payments';
            $this->breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Billing', 'url' => route('billing.invoices.index')],
                ['label' => 'Payments'],
            ];
        }
    }

    protected function buildQuery($forStats = false)
    {
        $query = Payment::withoutGlobalScopes()
            ->with(['customer', 'invoices'])
            ->where('gateway', '!=', 'reseller_topup');

        if ($this->isReseller) {
            $effectiveId = $this->effectiveResellerId;
            $query->where(function($q) use ($effectiveId) {
                $q->where('payments.reseller_id', $effectiveId)
                  ->orWhere('payments.created_by', $effectiveId)
                  ->orWhereHas('customer', function($cq) use ($effectiveId) {
                      $cq->withoutGlobalScopes()->where(function($sub) use ($effectiveId) {
                          $sub->where('customers.reseller_id', $effectiveId)
                              ->orWhere('customers.created_by', $effectiveId);
                      });
                  });
            });
        } elseif (!empty($this->filters['reseller_id']) && $this->filters['reseller_id'] !== 'all') {
            $resellerId = $this->filters['reseller_id'];
            $query->where(function($q) use ($resellerId) {
                $q->where('payments.reseller_id', $resellerId)
                  ->orWhere('payments.created_by', $resellerId)
                  ->orWhereHas('customer', function($cq) use ($resellerId) {
                      $cq->withoutGlobalScopes()->where(function($sub) use ($resellerId) {
                          $sub->where('customers.reseller_id', $resellerId)
                              ->orWhere('customers.created_by', $resellerId);
                      });
                  });
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($cq) {
                      $cq->withoutGlobalScopes()->where(function($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                      });
                  });
            });
        }

        if (!empty($this->filters['status']) && !$forStats) {
            $query->where('status', $this->filters['status']);
        }

        $methodFilter = $this->filters['method'] ?? ($this->filters['payment_method'] ?? '');
        if (!empty($methodFilter)) {
            $query->where('method', $methodFilter);
        }

        return $query;
    }

    public function export()
    {
        $this->authorize('export', Payment::class);

        $payments = $this->buildQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $filename = 'data-pembayaran-' . now()->format('YmdHis') . '.xlsx';

        return response()->streamDownload(function () use ($payments) {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new \App\Exports\PaymentExport($payments, $this->isReseller),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        $payments = $this->buildQuery()
                       ->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        $baseStatsQuery = $this->buildQuery(true);

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'total_amount' => (clone $baseStatsQuery)->sum('amount'),
            'success' => (clone $baseStatsQuery)->where('status', 'success')->count(),
            'pending' => (clone $baseStatsQuery)->where('status', 'pending')->count(),
            'failed' => (clone $baseStatsQuery)->where('status', 'failed')->count(),
        ];

        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        $resellers = $userQueryService->getResellers();

        return view('livewire.billing.payment.index', compact('payments', 'stats', 'resellers'));
    }
}
