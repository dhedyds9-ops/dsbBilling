<?php

namespace App\Livewire\Laporan\Pendapatan;

use App\Livewire\BaseEnterpriseList;
use App\Services\Laporan\PendapatanService;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class Index extends BaseEnterpriseList
{
    public string $activeTab = 'chart';
    public array $tabs = [
        'chart' => 'Chart Ringkasan',
        'comparison' => 'Comparison Periodik',
        'top_packages' => 'Top 10 Paket',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'laporan';
        $this->activePage = 'pendapatan';
        $this->filters = [
            'tahun' => (string) now()->year,
            'bulan' => (string) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'router_id' => '',
            'paket_id' => '',
            'reseller_id' => '',
        ];
    }

    public function boot(PendapatanService $svc): void
    {
        $this->svc = $svc;
    }

    public function getRowsQuery()
    {
        return $this->svc->topPackages($this->filters);
    }

    public function getRows()
    {
        return $this->svc->topPackages($this->filters);
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return 0;
    }

    public function exportCsv(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->svc->excelExport($this->filters);
        session()->flash('success', 'Export Excel disiapkan. ' . count($result['top_packages'] ?? []) . ' baris data pendapatan.');
        return back();
    }

    public function exportExcel(): void
    {
        $this->svc->excelExport($this->filters);
        session()->flash('success', 'Data Laporan Pendapatan berhasil di-export (Excel).');
        $this->dispatch('refreshPage');
    }

    public function exportPdf(): void
    {
        $this->svc->pdfExport($this->filters);
        session()->flash('success', 'Data Laporan Pendapatan berhasil di-export (PDF).');
        $this->dispatch('refreshPage');
    }

    public function refreshData(): void
    {
        $this->dispatch('refreshPage');
        session()->flash('info', 'Data Laporan Pendapatan telah disegarkan.');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getSummaryProperty(): array
    {
        return $this->svc->summary($this->filters);
    }

    public function getChartDailyProperty(): array
    {
        return $this->svc->chartDaily($this->filters);
    }

    public function getComparisonProperty(): array
    {
        return $this->svc->comparison($this->filters);
    }

    public function getTopPackagesProperty(): array
    {
        return $this->svc->topPackages($this->filters);
    }

    public function getFilterOptionsProperty(): array
    {
        return [
            'routers' => Router::pluck('name', 'id')->all(),
            'pakets' => ServiceProfile::pluck('name', 'id')->all(),
            'resellers' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['administrator', 'manager', 'reseller']))->pluck('name', 'id')->all(),
        ];
    }

    public function getToolbarActionsProperty(): array
    {
        return [
            ['label' => 'Export Excel', 'icon' => 'download', 'action' => 'exportExcel'],
            ['label' => 'Export PDF', 'icon' => 'printer', 'action' => 'exportPdf'],
            ['label' => 'Refresh', 'icon' => 'refresh-cw', 'action' => 'refreshData'],
        ];
    }

    public function render()
    {
        if (!Auth::check()) abort(403);

        return view('livewire.laporan.pendapatan.index', [
            'summary' => $this->summary,
            'chartDaily' => $this->chartDaily,
            'comparison' => $this->comparison,
            'topPackages' => $this->topPackages,
            'filterOptions' => $this->filterOptions,
            'toolbarActions' => $this->toolbarActions,
        ]);
    }
}
