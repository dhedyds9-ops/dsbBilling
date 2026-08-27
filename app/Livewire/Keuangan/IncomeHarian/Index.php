<?php

namespace App\Livewire\Keuangan\IncomeHarian;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\IncomeReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'income-harian';

    protected IncomeReportService $service;

    public function boot(IncomeReportService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'keuangan';
        $this->activePage = 'income-harian';
        $this->filters = [
            'start_date' => now()->subDays(6)->toDateString(),
            'end_date' => now()->toDateString(),
            'method' => '',
            'gateway' => '',
            'sales_id' => '',
        ];
        $this->tabs = [];
        $this->perPage = 31;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getRowsQuery()
    {
        $daily = $this->service->daily($this->filters);
        return collect($daily);
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $daily = $this->service->daily($this->filters);
            return collect($daily);
        }, 'Gagal memuat data Income Harian');
    }

    public function getDailySummaryProperty(): array
    {
        try {
            return $this->service->dailySummary($this->filters);
        } catch (\Throwable $e) {
            Log::error('IncomeHarian dailySummary failed', ['e' => $e->getMessage()]);
            return [
                'today_total' => 0,
                'today_count' => 0,
                'today_customers' => 0,
                'today_avg' => 0,
                'ytd' => 0,
            ];
        }
    }

    public function getChartDataProperty(): array
    {
        try {
            return $this->service->chart30Days();
        } catch (\Throwable $e) {
            Log::error('IncomeHarian chart30Days failed', ['e' => $e->getMessage()]);
            return [
                'labels' => [],
                'pppoe' => [],
                'hotspot' => [],
                'voucher' => [],
                'other' => [],
                'total' => [],
            ];
        }
    }

    public function getTopCustomersProperty(): array
    {
        try {
            return $this->service->topCustomers($this->filters, 10);
        } catch (\Throwable) {
            return [];
        }
    }

    public function getTopSalesProperty(): array
    {
        try {
            return $this->service->topSales($this->filters, 10);
        } catch (\Throwable) {
            return [];
        }
    }

    public function getPaymentMethodBreakdownProperty(): array
    {
        try {
            return $this->service->paymentMethodBreakdown($this->filters);
        } catch (\Throwable) {
            return [];
        }
    }

    public function getCashFlowMiniProperty(): array
    {
        try {
            return $this->service->cashFlowMini($this->filters);
        } catch (\Throwable) {
            return ['income' => 0, 'expense' => 0, 'net' => 0];
        }
    }

    public function getSalesOptionsProperty(): array
    {
        return $this->service->getSalesOptions();
    }

    public function getFilterConfigProperty(): array
    {
        return [
            ['key' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date'],
            ['key' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date'],
            ['key' => 'method', 'label' => 'Metode Bayar', 'type' => 'select', 'options' => [
                'bank_transfer' => 'Bank Transfer',
                'cash' => 'Cash',
                'e_wallet' => 'E-Wallet',
                'credit_card' => 'Credit Card',
            ]],
            ['key' => 'gateway', 'label' => 'Gateway', 'type' => 'select', 'options' => [
                'manual' => 'Manual',
                'midtrans' => 'Midtrans',
                'xendit' => 'Xendit',
                'tripay' => 'Tripay',
                'pppoe' => 'PPPoE',
                'hotspot' => 'Hotspot',
                'voucher' => 'Voucher',
            ]],
            ['key' => 'sales_id', 'label' => 'Sales', 'type' => 'select', 'options' => $this->salesOptions],
        ];
    }

    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'export', 'label' => 'Export CSV', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
        ];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return match ($action) {
            'export' => (function () {
                $this->exportCsv();
                return count($ids);
            })(),
            default => 0,
        };
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $rows = $this->service->daily($this->filters);
        return $this->service->exportCsvDaily($rows);
    }

    public function updatedSelected(array $value): void
    {
    }

    public function updatedSelectAll(bool $value): void
    {
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.keuangan.income-harian.index', [
            'rows' => $rows,
            'summary' => $this->dailySummary,
            'chartData' => $this->chartData,
            'topCustomers' => $this->topCustomers,
            'topSales' => $this->topSales,
            'paymentMethods' => $this->paymentMethodBreakdown,
            'cashFlow' => $this->cashFlowMini,
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
        ]);
    }
}
