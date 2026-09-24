<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Services\Billing\InvoiceService;

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

    public function isInvoiceLocked(): bool
    {
        if (!$this->invoice) return true;
        $original = $this->invoice->getOriginal();
        $status = $original['status'] ?? $this->invoice->status;
        $paid = (float) ($original['paid_amount'] ?? $this->invoice->paid_amount);
        return in_array($status, ['paid', 'partial'], true) || $paid > 0;
    }

    protected function rules()
    {
        return [
            'customer_id' => 'required|exists:members,id',
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $this->invoiceId,
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,unpaid,partial,paid,overdue,draft,cancelled,void',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function mount($id = null)
    {
        parent::mount();

        $this->invoiceId = $id;
        $this->invoice = Invoice::with('items')->findOrFail($id);

        $this->authorize('update', $this->invoice);

        $this->activeModule = 'billing';
        $this->activePage = 'invoices';

        $this->customer_id = $this->invoice->customer_id;
        $this->invoice_number = $this->invoice->invoice_number;
        $this->issue_date = $this->invoice->issue_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date->format('Y-m-d');
        $this->total_amount = (float) $this->invoice->total_amount;
        $this->currency = $this->invoice->currency;
        $this->status = $this->invoice->status;
        $this->items = $this->invoice->items->map(function($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
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
        if ($this->isInvoiceLocked()) {
            session()->flash('error', 'Invoice sudah dibayar, tidak dapat menambah item.');
            return;
        }
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem($index)
    {
        if ($this->isInvoiceLocked()) {
            session()->flash('error', 'Invoice sudah dibayar, tidak dapat mengubah item.');
            return;
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updatedItems($value, $key)
    {
        if ($this->isInvoiceLocked()) return;
        $path = explode('.', $key);
        if (count($path) === 2 && in_array($path[1], ['quantity', 'unit_price'], true)) {
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->items)->sum(function($item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            return $qty * $price;
        });
        foreach ($this->items as $idx => $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $this->items[$idx]['subtotal'] = $qty * $price;
        }
    }

    public function save(InvoiceService $invoiceService)
    {
        $locked = $this->isInvoiceLocked();
        $this->calculateTotal();
        $validated = $this->validate();

        if (!$locked) {
            $normalizedItems = collect($this->items)->map(function ($item) {
                return [
                    'description' => (string) $item['description'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                ];
            })->toArray();
        } else {
            $normalizedItems = collect($this->invoice->items)->map(function ($item) {
                return [
                    'description' => (string) $item->description,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ];
            })->toArray();
        }

        $invoiceService->updateInvoice(
            invoice: $this->invoice,
            customerId: (int) $this->customer_id,
            userId: auth()->id(),
            items: $normalizedItems,
            issueDate: new \DateTime($this->issue_date),
            dueDate: new \DateTime($this->due_date),
            invoiceNumber: $this->invoice_number,
            contractId: $this->invoice->contract_id,
            currency: $this->currency,
            status: $this->status,
        );

        session()->flash('success', 'Invoice berhasil diperbarui!');
        return redirect()->route('billing.invoices.show', $this->invoiceId);
    }

    public function render()
    {
        $customers = Customer::query()
            ->when(auth()->user()->hasRole('reseller'), function ($q) {
                $q->where(function ($qq) {
                    $qq->where('reseller_id', auth()->id())
                       ->orWhere('created_by', auth()->id());
                });
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'reseller_id', 'created_by']);

        return view('livewire.billing.invoice.edit', compact('customers'));
    }
}
