<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;

class Revenue extends AdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.revenue';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Laporan', 'url' => '#'],
            ['label' => 'Pendapatan', 'url' => route('reseller-portal.reports.revenue')],
        ];
    }

    public function render()
    {
        $resellerId = Auth::user()->getEffectiveResellerId();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Base query for successful payments this month
        $baseQuery = Payment::withoutGlobalScopes()->where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
              ->orWhereHas('customer', function ($cq) use ($resellerId) {
                  $cq->withoutGlobalScopes()->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
              });
        })->whereIn('status', ['success', 'approved', 'paid'])
          ->where('gateway', '!=', 'reseller_topup') // exclude topups
          ->whereMonth('created_at', $currentMonth)
          ->whereYear('created_at', $currentYear);

        $totalRevenue = (clone $baseQuery)->sum('amount');
        $transactionCount = (clone $baseQuery)->count();
        $averagePayment = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Recent payments
        $recentPayments = (clone $baseQuery)->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.reseller-portal.reports.revenue', [
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
            'averagePayment' => $averagePayment,
            'recentPayments' => $recentPayments,
            'currentMonthName' => now()->translatedFormat('F Y'),
        ]);
    }
}
