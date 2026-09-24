<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\Billing\BaseBillingComponent;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Billing\PaymentService;

class Index extends BaseBillingComponent
{
    public $showFilterModal = false;
    public $selectAll = false;
    public $selected = [];
    public string $activeTab = 'unpaid';

    public $showPaymentModal = false;
    public $paymentInvoiceId = null;
    public $paymentInvoiceTotal = 0;
    public $paymentAmount = 0;
    public $paymentMethod = 'cash';
    public $paymentReference = '';

    public function mount()
    {
        parent::mount();

        $this->authorize('viewAny', Invoice::class);

        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
                $reqFilters = request('filters', []);
        $defaultStatus = (isset($reqFilters['tahun']) || isset($reqFilters['bulan'])) ? '' : 'unpaid';
        
        $this->filters = [
            'status' => $reqFilters['status'] ?? $defaultStatus,
            'service_type' => $reqFilters['service_type'] ?? 'all',
            'reseller_id' => $reqFilters['reseller_id'] ?? 'all',
            'tahun' => $reqFilters['tahun'] ?? '',
            'bulan' => $reqFilters['bulan'] ?? ''
        ];
        $this->perPage = 10;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices'],
        ];
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function applyFilter()
    {
        $this->showFilterModal = false;
        $this->resetPage();
    }

    public function setTab($tab)
    {
        if (!in_array($tab, ['all', 'unpaid', 'paid', 'overdue'], true)) {
            $tab = 'unpaid';
        }
        $this->activeTab = $tab;
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    protected function buildScopedQuery()
    {
        $query = Invoice::query()->with(['customer.customerServices.serviceProfile', 'items', 'customer.reseller'])
            ->whereNotExists(function ($sub) {
                $sub->select('id')
                    ->from('voucher_orders')
                    ->whereColumn('voucher_orders.invoice_id', 'invoices.id');
            })
            ->when(auth()->user()->hasRole('reseller'), function($q) {
                $q->whereHas('customer', function($cq) {
                    $cq->where(function($qq) {
                        $qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if ($this->activeTab === 'unpaid') {
            $query->whereIn('status', ['pending', 'unpaid', 'partial', 'draft']);
        } elseif ($this->activeTab === 'paid') {
            $query->where('status', 'paid');
        } elseif ($this->activeTab === 'overdue') {
            $today = Carbon::today()->toDateString();
            $query->where(function ($q) use ($today) {
                $q->whereDate('due_date', '<', $today)
                  ->whereIn('status', ['pending', 'unpaid', 'partial', 'draft']);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

                if (!empty($this->filters['tahun'])) {
            $query->whereYear('issue_date', $this->filters['tahun']);
        }
        if (!empty($this->filters['bulan'])) {
            $query->whereMonth('issue_date', $this->filters['bulan']);
        }

        if (!empty($this->filters['reseller_id']) && $this->filters['reseller_id'] !== 'all') {
            $resellerId = $this->filters['reseller_id'];
            $query->whereHas('customer', function($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId);
            });
        }

        if (!empty($this->filters['service_type']) && $this->filters['service_type'] !== 'all') {
            $type = $this->filters['service_type'];
            $query->whereHas('customer.customerServices.serviceProfile', function($q) use ($type) {
                $q->where('service_type', $type);
            });
        }

        return $query;
    }

    
    public function openPaymentModal($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);
        
        $this->paymentInvoiceId = $invoice->id;
        $this->paymentInvoiceTotal = $invoice->total_amount - $invoice->paid_amount;
        $this->paymentAmount = $this->paymentInvoiceTotal;
        $this->paymentMethod = 'cash';
        $this->paymentReference = 'MANUAL-' . now()->format('YmdHi');
        
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->reset(['paymentInvoiceId', 'paymentAmount', 'paymentMethod', 'paymentReference', 'paymentInvoiceTotal']);
    }

    public function bulkPaymentModal()
    {
        if (empty($this->selected)) return;
        
        $invoices = Invoice::whereIn('id', array_map('intval', $this->selected))
            ->whereIn('status', ['unpaid', 'pending', 'partial', 'overdue'])
            ->get();
            
        if ($invoices->isEmpty()) {
            session()->flash('warning', 'Tidak ada tagihan yang bisa dibayar pada pilihan Anda.');
            return;
        }

        $this->paymentInvoiceId = null; // null indicates bulk payment
        $this->paymentInvoiceTotal = $invoices->sum(fn($i) => $i->total_amount - $i->paid_amount);
        $this->paymentAmount = $this->paymentInvoiceTotal;
        $this->paymentMethod = 'cash';
        $this->paymentReference = 'BULK-MANUAL-' . now()->format('YmdHi');
        
        $this->showPaymentModal = true;
    }

    public function submitPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentMethod' => 'required|string',
        ]);

        try {
            $paymentService = app(\App\Services\Billing\PaymentService::class);
            
            if ($this->paymentInvoiceId) {
                // Single Payment
                $invoice = Invoice::findOrFail($this->paymentInvoiceId);
                
                $paymentService->createPayment(
                    customerId: $invoice->customer_id,
                    amount: $this->paymentAmount,
                    userId: auth()->id(),
                    invoiceIds: [$invoice->id],
                    method: $this->paymentMethod,
                    status: 'success',
                    referenceNumber: $this->paymentReference ?: null,
                    gateway: 'manual'
                );
                
                session()->flash('success', 'Pembayaran berhasil dicatat!');
                return redirect()->route('billing.invoices.show', $this->paymentInvoiceId);
            } else {
                // Bulk Payment
                $invoices = Invoice::whereIn('id', array_map('intval', $this->selected))
                    ->whereIn('status', ['unpaid', 'pending', 'partial', 'overdue'])
                    ->get();
                
                if ($invoices->isEmpty()) {
                    session()->flash('warning', 'Tidak ada tagihan yang bisa dibayar.');
                    $this->closePaymentModal();
                    return;
                }

                // Group by customer_id because createPayment requires a single customer_id
                $groupedInvoices = $invoices->groupBy('customer_id');
                
                // If paymentAmount is exactly the total, we distribute exactly.
                // If it's less/more, we might need to handle it. For simplicity, bulk payments usually assume full payment.
                if ($this->paymentAmount < $this->paymentInvoiceTotal) {
                    session()->flash('error', 'Pembayaran bulk harus lunas sepenuhnya. (Nominal tidak sesuai)');
                    return;
                }
                
                DB::beginTransaction();
                foreach ($groupedInvoices as $customerId => $customerInvoices) {
                    $totalForCustomer = $customerInvoices->sum(fn($i) => $i->total_amount - $i->paid_amount);
                    
                    $paymentService->createPayment(
                        customerId: $customerId,
                        amount: $totalForCustomer,
                        userId: auth()->id(),
                        invoiceIds: $customerInvoices->pluck('id')->toArray(),
                        method: $this->paymentMethod,
                        status: 'success',
                        referenceNumber: $this->paymentReference ?: null,
                        gateway: 'manual'
                    );
                }
                DB::commit();

                $this->selected = [];
                $this->selectAll = false;
                $this->closePaymentModal();
                session()->flash('success', 'Pembayaran massal (' . $invoices->count() . ' tagihan) berhasil dicatat!');
            }
        } catch (\Exception $e) {
            if (isset($groupedInvoices)) {
                DB::rollBack();
            }
            session()->flash('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
            $this->closePaymentModal();
        }
    }

    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('delete', $invoice);

        if (in_array($invoice->status, ['paid', 'partial'], true)) {
            session()->flash('error', 'Tidak dapat menghapus invoice yang sudah dibayar (Lunas/Sebagian).');
            return;
        }

        $invoice->delete();
        $this->selected = array_values(array_diff($this->selected, [(string) $id]));
        $this->selectAll = false;
        session()->flash('success', 'Invoice berhasil dihapus!');
    }

    public function sendWhatsApp($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);

        try {
            app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->notifyInvoiceCreated($invoice);
            session()->flash('success', 'Tagihan berhasil dikirim ke WhatsApp pelanggan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim WhatsApp: ' . $e->getMessage());
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selected)) return;

        $scopedIds = $this->buildScopedQuery()
            ->whereIn('id', array_values(array_map('intval', $this->selected)))
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $count = 0;
        $skippedPaid = 0;
        $invoices = Invoice::whereIn('id', array_map('intval', $scopedIds))->get();
        foreach ($invoices as $invoice) {
            try {
                $this->authorize('delete', $invoice);
            } catch (\Throwable $e) {
                continue;
            }
            if (in_array($invoice->status, ['paid', 'partial'], true)) {
                $skippedPaid++;
                continue;
            }
            $invoice->delete();
            $count++;
        }

        $this->selected = [];
        $this->selectAll = false;

        if ($skippedPaid > 0) {
            session()->flash('warning', "$count invoice dihapus. $skippedPaid invoice dibayar dilewati.");
        } elseif ($count > 0) {
            session()->flash('success', "$count Invoice berhasil dihapus!");
        } else {
            session()->flash('info', 'Tidak ada invoice yang bisa dihapus.');
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->buildScopedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->limit(max(500, (int) $this->perPage * 10))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function export()
    {
        $this->authorize('export', Invoice::class);
        session()->flash('info', 'Fitur export invoice sedang disiapkan.');
    }

    public function render()
    {
        // sync activeTab from filters
        if (isset($this->filters['status'])) {
            $this->activeTab = empty($this->filters['status']) ? 'all' : $this->filters['status'];
        }

        $query = $this->buildScopedQuery();

        $invoices = (clone $query)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $today = Carbon::today()->toDateString();
        $statsQuery = $this->buildScopedQuery(true);
        $overdueQuery = $this->buildScopedQuery(true);

        $counts = (clone $statsQuery)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(total_amount) as total_amount")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid")
            ->selectRaw("SUM(CASE WHEN status IN ('pending','unpaid','partial','draft') THEN 1 ELSE 0 END) as pending")
            ->first();

        $overdueCount = (clone $overdueQuery)
            ->whereDate('due_date', '<', $today)
            ->whereIn('status', ['pending','unpaid','partial','draft'])
            ->count();

        $totalOutstanding = (clone $statsQuery)
            ->whereIn('status', ['pending','unpaid','partial','draft','overdue'])
            ->sum(DB::raw('total_amount - paid_amount'));

        $paidAmount = (clone $statsQuery)
            ->sum('paid_amount');

        $stats = [
            'total' => (int) ($counts->total ?? 0),
            'total_amount' => (float) ($counts->total_amount ?? 0),
            'paid' => (int) ($counts->paid ?? 0),
            'pending' => (int) ($counts->pending ?? 0),
            'overdue' => (int) $overdueCount,
            'outstanding' => (float) $totalOutstanding,
            'paid_amount' => (float) $paidAmount,
            'pokok' => (float) ($counts->total_amount ?? 0),
            'fee' => 0,
            'total' => (float) ($counts->total_amount ?? 0),
        ];

        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        $resellers = $userQueryService->getResellers();

        return view('livewire.billing.invoice.index', compact('invoices', 'stats', 'resellers'));
    }
}

