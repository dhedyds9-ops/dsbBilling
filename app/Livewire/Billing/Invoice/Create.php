<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Services\Billing\InvoiceService;
use App\Services\Auth\UserQueryService;
use Illuminate\Support\Facades\DB;

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
    public $contract_id;
    public $customer_services = [];
    public $selected_customer_service_id;

    protected function rules()
    {
        return [
            'customer_id' => 'required|exists:members,id',
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,unpaid,paid,draft',
            'contract_id' => 'nullable|integer|exists:contracts,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function mount()
    {
        parent::mount();

        $this->authorize('create', Invoice::class);

        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        $this->invoice_number = 'INV-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        $this->items = [[
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ]];

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices', 'url' => route('billing.invoices.index')],
            ['label' => 'Create'],
        ];
    }

    public function updatedCustomerId($value)
    {
        $this->customer_services = collect([]);
        $this->selected_customer_service_id = null;
        $this->contract_id = null;
        if (!empty($value)) {
            $customer = Customer::with(['customerServices.serviceProfile:id,name,download_speed_mbps,regular_price,service_type', 'contracts:id,customer_id,contract_number'])
                ->find($value);
            if ($customer) {
                $this->customer_services = $customer->customerServices;
                if ($customer->contracts && $customer->contracts->isNotEmpty()) {
                    $this->contract_id = $customer->contracts->first()->id;
                }
            }
        }
    }

    public function usePackageAsItem()
    {
        $svc = $this->customer_services->firstWhere('id', $this->selected_customer_service_id);
        if (!$svc || !$svc->serviceProfile) {
            session()->flash('error', 'Pilih paket layanan terlebih dahulu.');
            return;
        }
        $profile = $svc->serviceProfile;
        $price = (float) ($profile->regular_price ?? 0);
        $label = trim(sprintf(
            '%s%s',
            $profile->name ?? 'Paket Internet',
            $profile->download_speed_mbps ? ' ( ' . $profile->download_speed_mbps . ' Mbps )' : ''
        ));
        $this->items[] = [
            'description' => $label,
            'quantity' => 1,
            'unit_price' => $price,
            'subtotal' => $price,
        ];
        $this->calculateTotal();
        session()->flash('success', 'Item paket ditambahkan.');
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

    public function updatedItems($value, $key)
    {
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
            $sub = $qty * $price;
            return $sub;
        });
        foreach ($this->items as $idx => $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $this->items[$idx]['subtotal'] = $qty * $price;
        }
    }

    public function save(InvoiceService $invoiceService)
    {
        $this->calculateTotal();
        $validated = $this->validate();

        $normalizedItems = collect($this->items)->map(function ($item) {
            return [
                'description' => (string) $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
            ];
        })->toArray();

        $effectiveStatus = $this->status === 'pending' ? 'unpaid' : $this->status;

        $invoice = $invoiceService->createInvoice(
            customerId: (int) $this->customer_id,
            userId: auth()->id(),
            items: $normalizedItems,
            issueDate: new \DateTime($this->issue_date),
            dueDate: new \DateTime($this->due_date),
            invoiceNumber: $this->invoice_number,
            contractId: $this->contract_id ? (int) $this->contract_id : null,
            currency: $this->currency,
            status: $effectiveStatus,
        );

        session()->flash('success', 'Invoice berhasil dibuat!');
        return redirect()->route('billing.invoices.show', $invoice->id);
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

        return view('livewire.billing.invoice.create', compact('customers'));
    }
}
