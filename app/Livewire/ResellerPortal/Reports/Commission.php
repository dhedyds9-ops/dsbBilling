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
    public int $perPage = 10;
    public $selectedMonth;
    public $selectedYear;
    public string $filterType = 'all'; // all, pelanggan, voucher

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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function setFilterType(string $type)
    {
        $this->filterType = in_array($type, ['all', 'pelanggan', 'voucher']) ? $type : 'all';
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
        $allReportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_type' => 'all',
        ]);

        $allRows = $allReportData['rows'];
        $customerRows = $allRows->filter(fn($r) => !str_starts_with((string) $r['id'], 'VCR-'));
        $voucherRows = $allRows->filter(fn($r) => str_starts_with((string) $r['id'], 'VCR-'));

        $pelangganCount = $customerRows->count();
        $pelangganRevenue = (float) $customerRows->sum('harga_ppn');
        $voucherCount = $voucherRows->count();
        $voucherRevenue = (float) $voucherRows->sum('harga_ppn');

        $totalRevenueAll = (float) ($allReportData['summary']['total_ppn'] ?? ($pelangganRevenue + $voucherRevenue));
        $resellerCommissionAll = (float) ($allReportData['summary']['fee_seller'] ?? 0);
        $netCostAll = (float) ($allReportData['summary']['profit'] ?? 0);

        if ($this->filterType === 'pelanggan') {
            $activeRows = $customerRows;
            $grossRevenue = $pelangganRevenue;
            $netMargin = (float) $customerRows->sum('fee_seller');
            $totalCost = (float) $customerRows->sum('profit');
        } elseif ($this->filterType === 'voucher') {
            $activeRows = $voucherRows;
            $grossRevenue = $voucherRevenue;
            $netMargin = (float) $voucherRows->sum('fee_seller');
            $totalCost = (float) $voucherRows->sum('profit');
        } else {
            $activeRows = $allRows;
            $grossRevenue = $totalRevenueAll;
            $netMargin = $resellerCommissionAll;
            $totalCost = $netCostAll;
        }

        $profitMarginPercent = $grossRevenue > 0 ? ($netMargin / $grossRevenue) * 100 : 0;

        $currentPage = Paginator::resolveCurrentPage('page');
        $currentItems = $activeRows->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $details = new LengthAwarePaginator(
            $currentItems,
            $activeRows->count(),
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
            'allCount' => $allRows->count(),
            'pelangganCount' => $pelangganCount,
            'voucherCount' => $voucherCount,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
            'details' => $details,
        ]);
    }
}