<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class Sales extends AdminComponent
{
    public $selectedMonth;
    public $selectedYear;
    public string $filterType = 'all'; // all, pelanggan, voucher
    public int $perPage = 10;

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

        // Total active customers for this reseller
        $totalCustomers = Customer::withoutGlobalScopes()->where(function($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
        })->count();

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

        $thisMonthInvoices = $customerRows->count();
        $pelangganRevenue = (float) $customerRows->sum('harga_ppn');
        $totalVouchers = $voucherRows->count();
        $voucherRevenue = (float) $voucherRows->sum('harga_ppn');

        $totalSalesAll = (float) ($allReportData['summary']['total_ppn'] ?? ($pelangganRevenue + $voucherRevenue));
        $totalMarginAll = (float) ($allReportData['summary']['fee_seller'] ?? 0);
        $totalCostAll = (float) ($allReportData['summary']['profit'] ?? 0);

        // Filter rows by active tab
        if ($this->filterType === 'pelanggan') {
            $activeRows = $customerRows;
            $totalSalesMonth = $pelangganRevenue;
            $totalMarginMonth = (float) $customerRows->sum('fee_seller');
            $totalCostMonth = (float) $customerRows->sum('profit');
        } elseif ($this->filterType === 'voucher') {
            $activeRows = $voucherRows;
            $totalSalesMonth = $voucherRevenue;
            $totalMarginMonth = (float) $voucherRows->sum('fee_seller');
            $totalCostMonth = (float) $voucherRows->sum('profit');
        } else {
            $activeRows = $allRows;
            $totalSalesMonth = $totalSalesAll;
            $totalMarginMonth = $totalMarginAll;
            $totalCostMonth = $totalCostAll;
        }

        $currentPage = Paginator::resolveCurrentPage('page');
        $currentItems = $activeRows->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $paginatedSales = new LengthAwarePaginator(
            $currentItems,
            $activeRows->count(),
            $this->perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $monthDate = Carbon::createFromDate($year, $month, 1);

        return view('livewire.reseller-portal.reports.sales', [
            'totalCustomers' => $totalCustomers,
            'totalVouchers' => $totalVouchers,
            'voucherRevenue' => $voucherRevenue,
            'thisMonthInvoices' => $thisMonthInvoices,
            'pelangganRevenue' => $pelangganRevenue,
            'allCount' => $allRows->count(),
            'totalSalesMonth' => $totalSalesMonth,
            'totalMarginMonth' => $totalMarginMonth,
            'totalCostMonth' => $totalCostMonth,
            'paginatedSales' => $paginatedSales,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
        ]);
    }
}
