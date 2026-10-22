<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Revenue extends AdminComponent
{
    public string $filterType = 'all'; // all, pelanggan, voucher
    public $selectedDate;
    public string $periodMode = 'daily'; // daily, monthly
    public $selectedMonth;
    public $selectedYear;

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
    }

    public function setFilterType(string $type)
    {
        $this->filterType = in_array($type, ['all', 'pelanggan', 'voucher']) ? $type : 'all';
    }

    public function setToday()
    {
        $this->selectedDate = now()->toDateString();
        $this->periodMode = 'daily';
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
        $reportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $rows = $reportData['rows'];

        $customerRows = $rows->filter(fn($r) => !str_starts_with((string) $r['id'], 'VCR-'));
        $voucherRows = $rows->filter(fn($r) => str_starts_with((string) $r['id'], 'VCR-'));

        $pelangganRevenue = (float) $customerRows->sum('harga_ppn');
        $pelangganCount = $customerRows->count();

        $voucherRevenue = (float) $voucherRows->sum('harga_ppn');
        $voucherCount = $voucherRows->count();

        $totalRevenue = (float) ($reportData['summary']['total_ppn'] ?? ($pelangganRevenue + $voucherRevenue));
        $transactionCount = $pelangganCount + $voucherCount;
        $averagePayment = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Filter transactions for table
        if ($this->filterType === 'pelanggan') {
            $filteredTransactions = $customerRows;
        } elseif ($this->filterType === 'voucher') {
            $filteredTransactions = $voucherRows;
        } else {
            $filteredTransactions = $rows;
        }

        $recentTransactions = $filteredTransactions->take(50);
        $monthDate = Carbon::createFromDate($year, $month, 1);

        return view('livewire.reseller-portal.reports.revenue', [
            'totalRevenue' => $totalRevenue,
            'pelangganRevenue' => $pelangganRevenue,
            'pelangganCount' => $pelangganCount,
            'voucherRevenue' => $voucherRevenue,
            'voucherCount' => $voucherCount,
            'transactionCount' => $transactionCount,
            'averagePayment' => $averagePayment,
            'recentTransactions' => $recentTransactions,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
            'periodLabel' => $periodLabel,
        ]);
    }
}
