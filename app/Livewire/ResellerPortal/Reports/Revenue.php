<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class Revenue extends AdminComponent
{
    public string $filterType = 'all'; // all, pelanggan, voucher
    public $selectedDate;
    public string $periodMode = 'daily'; // daily, monthly
    public $selectedMonth;
    public $selectedYear;
    public int $perPage = 10;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.revenue';
        
        $this->selectedDate = now()->toDateString();
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Pemasukan Harian', 'url' => route('reseller-portal.reports.revenue')],
        ];
    }

    public function setPeriodMode(string $mode)
    {
        $this->periodMode = in_array($mode, ['daily', 'monthly']) ? $mode : 'daily';
        $this->resetPage();
    }

    public function setFilterType(string $type)
    {
        $this->filterType = in_array($type, ['all', 'pelanggan', 'voucher']) ? $type : 'all';
        $this->resetPage();
    }

    public function setToday()
    {
        $this->selectedDate = now()->toDateString();
        $this->periodMode = 'daily';
        $this->resetPage();
    }

    public function setYesterday()
    {
        $this->selectedDate = now()->subDay()->toDateString();
        $this->periodMode = 'daily';
        $this->resetPage();
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
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

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        $month = (int) ($this->selectedMonth ?: now()->month);
        $year = (int) ($this->selectedYear ?: now()->year);

        if ($this->periodMode === 'daily') {
            $date = !empty($this->selectedDate) ? Carbon::parse($this->selectedDate)->format('Y-m-d') : now()->toDateString();
            $startDate = $date;
            $endDate = $date;
            $periodLabel = Carbon::parse($date)->translatedFormat('l, d F Y');
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $periodLabel = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        }

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

        // Total for entire period
        $totalRevenueAll = (float) ($allReportData['summary']['total_ppn'] ?? ($pelangganRevenue + $voucherRevenue));
        $resellerCommissionAll = (float) ($allReportData['summary']['fee_seller'] ?? 0);
        $netCostAll = (float) ($allReportData['summary']['profit'] ?? 0);

        // Filter rows by active tab
        if ($this->filterType === 'pelanggan') {
            $activeRows = $customerRows;
            $totalRevenue = $pelangganRevenue;
            $resellerCommission = (float) $customerRows->sum('fee_seller');
            $netCost = (float) $customerRows->sum('profit');
        } elseif ($this->filterType === 'voucher') {
            $activeRows = $voucherRows;
            $totalRevenue = $voucherRevenue;
            $resellerCommission = (float) $voucherRows->sum('fee_seller');
            $netCost = (float) $voucherRows->sum('profit');
        } else {
            $activeRows = $allRows;
            $totalRevenue = $totalRevenueAll;
            $resellerCommission = $resellerCommissionAll;
            $netCost = $netCostAll;
        }

        $currentPage = Paginator::resolveCurrentPage('page');
        $currentItems = $activeRows->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $paginatedRows = new LengthAwarePaginator(
            $currentItems,
            $activeRows->count(),
            $this->perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.reseller-portal.reports.revenue', [
            'totalRevenue' => $totalRevenue,
            'resellerCommission' => $resellerCommission,
            'netCost' => $netCost,
            'allCount' => $allRows->count(),
            'pelangganCount' => $pelangganCount,
            'pelangganRevenue' => $pelangganRevenue,
            'voucherCount' => $voucherCount,
            'voucherRevenue' => $voucherRevenue,
            'periodLabel' => $periodLabel,
            'paginatedRows' => $paginatedRows,
        ]);
    }
}
