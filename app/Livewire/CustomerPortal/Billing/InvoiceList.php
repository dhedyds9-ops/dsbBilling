<?php

namespace App\Livewire\CustomerPortal\Billing;

use App\Services\CustomerPortal\CustomerBillingService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public function render(CustomerBillingService $billingService)
    {
        $invoices = $billingService->getPaginatedInvoices(Auth::id());
        return view('livewire.customer-portal.billing.invoice-list', compact('invoices'));
    }
}

