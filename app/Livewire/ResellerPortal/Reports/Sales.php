<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Sales extends AdminComponent
{
    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.sales';
        
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Pemasukan Bulanan', 'url' => route('reseller-portal.reports.sales')],
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        $month = (int) ($this->selectedMonth ?: now()->month);
        $year = (int) ($this->selectedYear ?: now()->year);

        // Total active customers for this reseller
        $totalCustomers = Customer::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
        })->count();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $incomeService = app(IncomeReportService::class);
        $reportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $rows = $reportData['rows'];
        $customerRows = $rows->filter(fn($r) => !str_starts_with((string) $r['id'], 'VCR-'));
        $voucherRows = $rows->filter(fn($r) => str_starts_with((string) $r['id'], 'VCR-'));

        $totalVouchers = $voucherRows->count();
        $thisMonthInvoices = $customerRows->count();
        $totalSalesMonth = (float) ($reportData['summary']['total_plus_ppn'] ?? 0);

        // Top 10 recent sales transactions
        $recentSales = $rows->take(10);
        $monthDate = Carbon::createFromDate($year, $month, 1);

        return view('livewire.reseller-portal.reports.sales', [
            'totalCustomers' => $totalCustomers,
            'totalVouchers' => $totalVouchers,
            'thisMonthInvoices' => $thisMonthInvoices,
            'totalSalesMonth' => $totalSalesMonth,
            'recentSales' => $recentSales,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
        ]);
    }
}
