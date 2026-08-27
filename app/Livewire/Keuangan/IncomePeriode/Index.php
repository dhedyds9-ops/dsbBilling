<?php

namespace App\Livewire\Keuangan\IncomePeriode;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\IncomeReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'income-periode';

    protected IncomeReportService $service;

    public function boot(IncomeReportService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'keuangan';
        $this->activePage = 'income-periode';
        $this->tabs = [
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
        ];
        $this->filters = [
            'year' => (string) now()->year,
            'month' => (string) now()->month,
            'start_date' => '',
            'end_date' => '',
        ];
        $this->perPage = 50;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getRowsQuery()
    {
        $data = $this->service->periodComparison($this->activeTab ?: 'monthly', $this->filters);
        return collect($data['rows'] ?? []);
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $data = $this->getPeriodData();
            return collect($data['rows'] ?? []);
        }, 'Gagal memuat data Income Periode');
    }

    public function getPeriodDataProperty(): array
    {
        try {
            return $this->service->periodComparison($this->activeTab ?: 'monthly', $this->filters);
        } catch (\Throwable $e) {
            Log::error('IncomePeriode periodComparison failed', ['e' => $e->getMessage()]);
            return [
                'type' => $this->activeTab ?: 'monthly',
                'period_label' => '-',
                'prev_period_label' => '-',
                'current_total' => 0,
                'prev_total' => 0,
                'growth_percent' => 0,
                'labels' => [],
                'current_data' => [],
                'prev_data' => [],
                'rows' => [],
            ];
        }
    }

    protected function getPeriodData(): array
    {
        return $this->periodData;
    }

    public function getFilterConfigProperty(): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[(string) $m] = now()->setMonth($m)->translatedFormat('F');
        }
        $years = [];
        $y = now()->year;
        for ($i = 0; $i < 5; $i++) {
            $years[(string) ($y - $i)] = (string) ($y - $i);
        }

        $result = [
            ['key' => 'year', 'label' => 'Tahun', 'type' => 'select', 'options' => $years],
        ];
        if (($this->activeTab ?: 'monthly') === 'monthly') {
            $result[] = ['key' => 'month', 'label' => 'Bulan', 'type' => 'select', 'options' => $months];
        }
        $result[] = ['key' => 'start_date', 'label' => 'Rentang Mulai', 'type' => 'date'];
        $result[] = ['key' => 'end_date', 'label' => 'Rentang Akhir', 'type' => 'date'];
        return $result;
    }

    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'export', 'label' => 'Export Excel', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
        ];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return match ($action) {
            'export' => (function () {
                $this->exportExcelAction();
                return count($ids);
            })(),
            default => 0,
        };
    }

    public function exportExcelAction(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $userId = Auth::id() ?? 1;
        return $this->service->excelExport([
            'type' => $this->activeTab ?: 'monthly',
            'filters' => $this->filters,
        ], $userId);
    }

    public function exportPdfAction(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $userId = Auth::id() ?? 1;
        return $this->service->pdfExport([
            'type' => $this->activeTab ?: 'monthly',
            'filters' => $this->filters,
        ], $userId);
    }

    public function exportRowExcel(string $periodLabel): void
    {
        $userId = Auth::id() ?? 1;
        try {
            $this->service->excelExport([
                'type' => $this->activeTab ?: 'monthly',
                'filters' => array_merge($this->filters, ['period_label' => $periodLabel]),
            ], $userId);
            session()->flash('success', 'Excel untuk periode ' . $periodLabel . ' sedang diproses.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal export: ' . $e->getMessage();
        }
    }

    public function exportRowPdf(string $periodLabel): void
    {
        try {
            $this->service->pdfExport([
                'type' => $this->activeTab ?: 'monthly',
                'filters' => array_merge($this->filters, ['period_label' => $periodLabel]),
            ], Auth::id() ?? 1);
            session()->flash('success', 'PDF untuk periode ' . $periodLabel . ' sedang diproses.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal export: ' . $e->getMessage();
        }
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        return $this->exportExcelAction();
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
        $periodData = $this->periodData;
        return view('livewire.keuangan.income-periode.index', [
            'rows' => $rows,
            'periodData' => $periodData,
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
        ]);
    }
}
