<?php

namespace App\Services\CustomerPortal;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Payment\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CustomerBillingService
{
    public function getPaginatedInvoices(int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::with('items')
            ->where('customer_id', $customerId)
            ->latest('due_date')
            ->paginate($perPage);
    }

    public function getInvoiceDetails(int $customerId, int $invoiceId): ?Invoice
    {
        return Invoice::with(['items', 'payments'])
            ->where('customer_id', $customerId)
            ->where('id', $invoiceId)
            ->first();
    }

    public function getPaymentHistory(int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return Payment::with('invoice')
            ->whereHas('invoice', fn($q) => $q->where('customer_id', $customerId))
            ->latest('payment_date')
            ->paginate($perPage);
    }
}

