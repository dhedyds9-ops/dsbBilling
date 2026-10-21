<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class Commission extends AdminComponent
{
    public $perPage = 10;
    public $selectedMonth;
    public $selectedYear;
    public string $filterType = 'all'; // all, customer, voucher

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.commission';
        
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Komisi & Margin', 'url' => route('reseller-portal.reports.commission')],
        ];
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        $month = (int) ($this->selectedMonth ?: now()->month);
        $year = (int) ($this->selectedYear ?: now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $incomeService = app(IncomeReportService::class);
        $reportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_type' => $this->filterType,
        ]);

        $summary = $reportData['summary'];
        $grossRevenue = (float) ($summary['total_plus_ppn'] ?? 0);
        $netMargin = (float) ($summary['fee_seller'] ?? 0);
        $totalCost = (float) ($summary['profit'] ?? 0);
        $profitMarginPercent = $grossRevenue > 0 ? ($netMargin / $grossRevenue) * 100 : 0;

        $rows = $reportData['rows'];
        $currentPage = Paginator::resolveCurrentPage('page');
        $currentItems = $rows->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $details = new LengthAwarePaginator(
            $currentItems,
            $rows->count(),
            $this->perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $monthDate = Carbon::createFromDate($year, $month, 1);

        return view('livewire.reseller-portal.reports.commission', [
            'grossRevenue' => $grossRevenue,
            'totalCost' => $totalCost,
            'netMargin' => $netMargin,
            'profitMarginPercent' => $profitMarginPercent,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
            'details' => $details,
        ]);
    }
}