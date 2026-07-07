<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\Billing\BaseBillingComponent;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;

class Index extends BaseBillingComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices'],
        ];
    }

    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        session()->flash('success', 'Invoice berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = Invoice::query()->with(['customer', 'items']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $invoices = $query->orderBy($this->sortField, $this->sortDirection)
                       ->paginate($this->perPage);

        $stats = [
            'total' => Invoice::count(),
            'total_amount' => Invoice::sum('total_amount'),
            'paid' => Invoice::where('status', 'paid')->count(),
            'pending' => Invoice::where('status', 'pending')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
        ];

        return view('livewire.billing.invoice.index', compact('invoices', 'stats'));
    }
}
