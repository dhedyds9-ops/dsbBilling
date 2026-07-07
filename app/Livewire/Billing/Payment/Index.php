<?php

namespace App\Livewire\Billing\Payment;

use App\Livewire\Billing\BaseBillingComponent;
use App\Models\Payment\Payment;
use App\Models\Customer;

class Index extends BaseBillingComponent
{
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

    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        session()->flash('success', 'Payment berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Payment::with(['customer']);

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

        $payments = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        $stats = [
            'total' => Payment::count(),
            'total_amount' => Payment::sum('amount'),
            'success' => Payment::where('status', 'success')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
        ];

        return view('livewire.billing.payment.index', compact('payments', 'stats'));
    }
}
