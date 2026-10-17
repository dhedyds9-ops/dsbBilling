<?php

namespace App\Livewire\ResellerPortal\Billing;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Invoices extends AdminComponent
{
    public $showDetailModal = false;
    public $showPaymentModal = false;
    public $paymentInvoiceId = null;
    public $paymentInvoiceTotal = 0;
    public $paymentAmount = 0;
    public $paymentMethod = 'cash';
    public $selectedInvoice = null;
    public $search = '';
    public $perPage = 10;
    public $status = '';
    public $dateRange = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.billing.invoices';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Tagihan', 'url' => '#'],
            ['label' => 'Tagihan Pelanggan', 'url' => route('reseller-portal.billing.invoices')],
        ];
    }

    public function viewDetail($id)
    {
        $resellerId = Auth::user()->getEffectiveResellerId();
        $this->selectedInvoice = Invoice::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function ($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'items'])->find($id);

        if ($this->selectedInvoice) {
            $this->showDetailModal = true;
        }
    }

    public function openPaymentModal($id)
    {
        $resellerId = Auth::user()->getEffectiveResellerId();
        $invoice = Invoice::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function ($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->findOrFail($id);

        $this->paymentInvoiceId = $invoice->id;
        $this->paymentInvoiceTotal = max(0, $invoice->total_amount - $invoice->paid_amount);
        $this->paymentAmount = $this->paymentInvoiceTotal;
        $this->paymentMethod = 'cash';
        $this->showPaymentModal = true;
        
        // Hide detail modal if it's open
        $this->showDetailModal = false;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentInvoiceId = null;
    }

    public function submitPayment()
    {
        try {
            if ($this->paymentAmount <= 0) {
                throw new \Exception('Jumlah bayar harus lebih dari 0.');
            }

            $resellerId = Auth::user()->getEffectiveResellerId();
            $invoice = Invoice::withoutGlobalScopes()->where(function($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', function ($cq) use ($resellerId) {
                      $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
                  });
            })->find($this->paymentInvoiceId);

            if (!$invoice) {
                throw new \Exception("Tagihan invoice #{$this->paymentInvoiceId} tidak ditemukan atau Anda tidak memiliki akses.");
            }
            
            app(\App\Services\Billing\PaymentService::class)->createPayment(
                customerId: $invoice->customer_id,
                amount: $this->paymentAmount,
                userId: $resellerId,
                invoiceIds: [$invoice->id],
                currency: 'IDR',
                method: $this->paymentMethod,
                status: 'success',
                gateway: 'manual'
            );

            $this->closePaymentModal();
            $this->dispatch('swal:success', ['title' => 'Berhasil', 'text' => 'Pembayaran berhasil disimpan.']);
            return redirect()->route('reseller-portal.billing.invoices.show', $invoice->id);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $resellerId = Auth::user()->getEffectiveResellerId();

        // Start querying invoices for customers belonging to this reseller
        $query = Invoice::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function ($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'items']);

        // Search by Invoice Number or Customer Name / Code / Phone
        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($subQ) {
                      $subQ->withoutGlobalScopes()->where(function($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('code', 'like', '%' . $this->search . '%')
                             ->orWhere('phone', 'like', '%' . $this->search . '%');
                      });
                  });
            });
        }

        // Filter by Status
        if ($this->status) {
            $query->where('status', $this->status);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.reseller-portal.billing.invoices', [
            'invoices' => $invoices
        ]);
    }
}
