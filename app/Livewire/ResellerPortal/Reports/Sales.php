<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\User;
use App\Models\ISP\Voucher;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\Auth;

class Sales extends AdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.sales';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Laporan', 'url' => '#'],
            ['label' => 'Penjualan', 'url' => route('reseller-portal.reports.sales')],
        ];
    }

    public function render()
    {
        $resellerId = Auth::user()->getEffectiveResellerId();

        // Total active customers
        $totalCustomers = \App\Models\CRM\Customer::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
        })->count();

        // Total vouchers (used)
        $totalVouchers = Voucher::withoutGlobalScopes()->where('reseller_id', $resellerId)->where('status', 'used')->count();

        // Total invoices this month
        $thisMonthInvoices = Invoice::withoutGlobalScopes()->where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();

        // Recent successful sales (paid invoices)
        $recentSales = Invoice::withoutGlobalScopes()->where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->where('status', 'paid')
          ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
          ->orderBy('updated_at', 'desc')
          ->take(5)
          ->get();

        return view('livewire.reseller-portal.reports.sales', [
            'totalCustomers' => $totalCustomers,
            'totalVouchers' => $totalVouchers,
            'thisMonthInvoices' => $thisMonthInvoices,
            'recentSales' => $recentSales,
        ]);
    }
}
