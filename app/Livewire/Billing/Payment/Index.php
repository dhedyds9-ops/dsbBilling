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

    public function delete($id)
    {
        $payment = Payment::with('invoices')->findOrFail($id);
        
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

        $payments = Payment::with('invoices')->whereIn('id', array_map('intval', $this->selected))->get();
        $count = 0;
        
        foreach ($payments as $payment) {
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

    protected function buildQuery()
    {
        $query = Payment::with(['customer'])
            ->when(auth()->user()->hasRole('reseller'), function($q) {
                $q->whereHas('customer', function($cq) {
                    $cq->where(function($qq) {
                        $qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['method']) {
            $query->where('method', $this->filters['method']);
        }

        return $query;
    }
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'payments';
        $this->filters = ['status' => '', 'method' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Payments'],
        ];
    }



    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $payments = $this->buildQuery()
                       ->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        $baseStatsQuery = Payment::query()->when(auth()->user()->hasRole('reseller'), function($q) {
            $q->whereHas('customer', function($cq) {
                $cq->where(function($qq) {
                    $qq->where('reseller_id', auth()->id())
                       ->orWhere('created_by', auth()->id());
                });
            });
        });

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'total_amount' => (clone $baseStatsQuery)->sum('amount'),
            'success' => (clone $baseStatsQuery)->where('status', 'success')->count(),
            'pending' => (clone $baseStatsQuery)->where('status', 'pending')->count(),
            'failed' => (clone $baseStatsQuery)->where('status', 'failed')->count(),
        ];

        return view('livewire.billing.payment.index', compact('payments', 'stats'));
    }
}
