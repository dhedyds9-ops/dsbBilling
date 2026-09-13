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
        $resellerId = Auth::id();

        // Total active customers
        $totalCustomers = User::where('reseller_id', $resellerId)->count();

        // Total vouchers (used)
        $totalVouchers = Voucher::where('reseller_id', $resellerId)->where('status', 'used')->count();

        // Total invoices this month
        $thisMonthInvoices = Invoice::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();

        // Recent successful sales (paid invoices)
        $recentSales = Invoice::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })->where('status', 'paid')
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
