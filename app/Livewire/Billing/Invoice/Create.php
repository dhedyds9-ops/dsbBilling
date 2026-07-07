<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\Customer;

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
            'total' => 0,
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

    public function save()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        $invoice = Invoice::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'customer_id' => $this->customer_id,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'total_amount' => $this->total_amount,
            'paid_amount' => 0,
            'currency' => $this->currency,
            'status' => $this->status,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }

        session()->flash('success', 'Invoice berhasil dibuat!');
        return redirect()->route('billing.invoices.show', $invoice->id);
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('livewire.billing.invoice.create', compact('customers'));
    }
}
