<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Services\Billing\InvoiceService;

class Create extends AdminComponent
{
    public $customer_id;
    public $invoice_number;
    public $issue_date;
    public $due_date;
    public $total_amount = 0;
    public $currency = 'IDR';
    public $status = 'pending';
    public $items = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->invoice_number = 'INV-' . now()->format('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices', 'url' => route('billing.invoices.index')],
            ['label' => 'Create'],
        ];
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->items)->sum(function($item) {
            return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        });
    }

    public function save(InvoiceService $invoiceService)
    {
        $this->validate([
            'customer_id' => 'required|exists:members,id',
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $normalizedItems = collect($this->items)->map(function ($item) {
            return [
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ];
        })->toArray();

        $invoice = $invoiceService->createInvoice(
            customerId: (int) $this->customer_id,
            userId: auth()->id(),
            items: $normalizedItems,
            issueDate: new \DateTime($this->issue_date),
            dueDate: new \DateTime($this->due_date),
            invoiceNumber: $this->invoice_number,
            contractId: null,
            currency: $this->currency,
            status: $this->status === 'pending' ? 'unpaid' : $this->status,
        );

        session()->flash('success', 'Invoice berhasil dibuat!');
        return redirect()->route('billing.invoices.show', $invoice->id);
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('livewire.billing.invoice.create', compact('customers'));
    }
}
