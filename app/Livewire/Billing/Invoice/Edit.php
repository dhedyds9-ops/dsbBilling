<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\Customer;

class Edit extends AdminComponent
{
    public $invoiceId;
    public $invoice;
    public $customer_id;
    public $invoice_number;
    public $issue_date;
    public $due_date;
    public $total_amount = 0;
    public $currency = 'IDR';
    public $status;
    public $items = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->invoiceId = $id;
        $this->invoice = Invoice::with('items')->findOrFail($id);
        
        $this->customer_id = $this->invoice->customer_id;
        $this->invoice_number = $this->invoice->invoice_number;
        $this->issue_date = $this->invoice->issue_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date->format('Y-m-d');
        $this->total_amount = $this->invoice->total_amount;
        $this->currency = $this->invoice->currency;
        $this->status = $this->invoice->status;
        $this->items = $this->invoice->items->map(function($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ];
        })->toArray();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices', 'url' => route('billing.invoices.index')],
            ['label' => $this->invoice->invoice_number, 'url' => route('billing.invoices.show', $this->invoiceId)],
            ['label' => 'Edit'],
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
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $this->invoiceId,
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        $this->invoice->update([
            'customer_id' => $this->customer_id,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'updated_by' => auth()->id(),
        ]);

        $this->invoice->items()->delete();
        foreach ($this->items as $item) {
            $this->invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }

        session()->flash('success', 'Invoice berhasil diperbarui!');
        return redirect()->route('billing.invoices.show', $this->invoiceId);
    }

    public function render()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('livewire.billing.invoice.edit', compact('customers'));
    }
}
