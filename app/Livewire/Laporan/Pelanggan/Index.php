<?php

namespace App\Livewire\Laporan\Pelanggan;

use App\Livewire\BaseEnterpriseList;
use App\Services\Laporan\CustomerReportService;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class Index extends BaseEnterpriseList
{
    public string $activeTab = 'growth';
    public array $tabs = [
        'growth' => 'Customer Growth',
        'activations' => 'Activation',
        'suspensions' => 'Suspension',
        'terminations' => 'Termination',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'laporan';
        $this->activePage = 'pelanggan';
        $this->filters = [
            'tahun' => (string) now()->year,
            'bulan' => (string) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'router_id' => '',
            'paket_id' => '',
            'wilayah' => '',
            'sales_id' => '',
            'reseller_id' => '',
        ];
    }

    public function boot(CustomerReportService $svc): void
    {
        $this->svc = $svc;
    }

    public function getRowsQuery()
    {
        return collect($this->svc->growth($this->filters)['rows'] ?? []);
    }

    public function getRows()
    {
        return $this->svc->growth($this->filters)['rows'] ?? [];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return 0;
    }

    public function exportCsv(): \Illuminate\Http\RedirectResponse
    {
        $this->svc->excelExport($this->activeTab);
        session()->flash('success', 'Export data pelanggan tab ' . $this->activeTab . ' disiapkan.');
        return back();
    }

    public function exportExcel(): void
    {
        $this->svc->excelExport($this->activeTab);
        session()->flash('success', 'Export Excel Laporan Pelanggan (' . $this->tabs[$this->activeTab] . ') berhasil.');
        $this->dispatch('refreshPage');
    }

    public function refreshData(): void
    {
        $this->dispatch('refreshPage');
        session()->flash('info', 'Data Laporan Pelanggan disegarkan.');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getSummaryProperty(): array
    {
        $now = Carbon::now();
        $startMonth = $now->copy()->startOfMonth();
        $baseQuery = \App\Models\CRM\Customer::query();
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('reseller')) {
            $baseQuery->where('created_by', \Illuminate\Support\Facades\Auth::id());
        }

        $aktif = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->count();
        $baru = (clone $baseQuery)->whereBetween('created_at', [$startMonth, $now])->count();
        $suspend = (clone $baseQuery)->where('status', 'suspended')
            ->whereBetween('updated_at', [$startMonth, $now])
            ->count();
        $awalAktif = (clone $baseQuery)->whereDate('created_at', '<', $startMonth)
            ->where(function ($q) { $q->where('status', 'active')->orWhereNull('status'); })
            ->count() + 1;
        $terminated = (clone $baseQuery)->where('status', 'terminated')
            ->whereBetween('updated_at', [$startMonth, $now])
            ->count();
        $churn = round(($terminated / $awalAktif) * 100, 2);

        return [
            'total_aktif' => $aktif,
            'baru_bulan_ini' => $baru,
            'suspend_bulan_ini' => $suspend,
            'churn_rate_pct' => $churn,
        ];
    }

    public function getGrowthProperty(): array
    {
        return $this->svc->growth($this->filters);
    }

    public function getActivationsProperty(): array
    {
        return $this->svc->activations($this->filters);
    }

    public function getSuspensionsProperty(): array
    {
        return $this->svc->suspensions($this->filters);
    }

    public function getTerminationsProperty(): array
    {
        return $this->svc->terminations($this->filters);
    }

    public function getFilterOptionsProperty(): array
    {
        return [
            'routers' => Router::pluck('name', 'id')->all(),
            'pakets' => ServiceProfile::pluck('name', 'id')->all(),
            'wilayahs' => ['jakarta' => 'Jakarta', 'bandung' => 'Bandung', 'surabaya' => 'Surabaya', 'yogyakarta' => 'Yogyakarta', 'semarang' => 'Semarang'],
            'sales' => User::pluck('name', 'id')->all(),
            'resellers' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['administrator', 'manager', 'reseller']))->pluck('name', 'id')->all(),
        ];
    }

    public function getToolbarActionsProperty(): array
    {
        return [
            ['label' => 'Export Excel', 'icon' => 'download', 'action' => 'exportExcel'],
            ['label' => 'Refresh', 'icon' => 'refresh-cw', 'action' => 'refreshData'],
        ];
    }

    public function render()
    {
        if (!Auth::check()) abort(403);

        return view('livewire.laporan.pelanggan.index', [
            'summary' => $this->summary,
            'growth' => $this->growth,
            'activations' => $this->activations,
            'suspensions' => $this->suspensions,
            'terminations' => $this->terminations,
            'filterOptions' => $this->filterOptions,
            'toolbarActions' => $this->toolbarActions,
        ]);
    }
}
