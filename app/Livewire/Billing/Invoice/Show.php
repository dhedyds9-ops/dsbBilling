<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;

class Show extends AdminComponent
{
    public $invoiceId;
    public $invoice;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->invoiceId = $id;
        $this->invoice = Invoice::with(['customer', 'items', 'payments'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices', 'url' => route('billing.invoices.index')],
            ['label' => $this->invoice->invoice_number],
        ];
    }

    public function render()
    {
        $timeline = [
            ['date' => $this->invoice->created_at, 'title' => 'Invoice Dibuat', 'description' => 'Invoice berhasil dibuat', 'type' => 'create'],
        ];
        
        $activities = [
            ['user' => 'Admin', 'action' => 'Membuat invoice', 'module' => 'Billing', 'time' => 'Baru saja'],
        ];

        return view('livewire.billing.invoice.show', compact('timeline', 'activities'));
    }
}
