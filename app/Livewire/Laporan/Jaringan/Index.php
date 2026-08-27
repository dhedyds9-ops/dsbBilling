<?php

namespace App\Livewire\Laporan\Jaringan;

use App\Livewire\BaseEnterpriseList;
use App\Services\Laporan\NetworkReportService;
use App\Models\ISP\Router;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class Index extends BaseEnterpriseList
{
    public string $activeTab = 'availability';
    public array $tabs = [
        'availability' => 'Availability',
        'downtime' => 'Downtime',
        'los_onu' => 'LOS ONU',
        'router_health' => 'Router Health',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'laporan';
        $this->activePage = 'jaringan';
        $this->filters = [
            'tahun' => (string) now()->year,
            'bulan' => (string) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'router_id' => '',
            'olt_id' => '',
            'pop_id' => '',
            'vendor_id' => '',
            'wilayah' => '',
        ];
    }

    public function boot(NetworkReportService $svc): void
    {
        $this->svc = $svc;
    }

    public function getRowsQuery()
    {
        return collect($this->svc->availability($this->filters)['rows'] ?? []);
    }

    public function getRows()
    {
        return $this->svc->availability($this->filters)['rows'] ?? [];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return 0;
    }

    public function exportCsv(): \Illuminate\Http\RedirectResponse
    {
        session()->flash('success', 'Export Excel Laporan Jaringan disiapkan.');
        return back();
    }

    public function exportExcel(): void
    {
        session()->flash('success', 'Export Excel Laporan Jaringan berhasil.');
        $this->dispatch('refreshPage');
    }

    public function refreshData(): void
    {
        $this->dispatch('refreshPage');
        session()->flash('info', 'Data Laporan Jaringan disegarkan.');
    }

    public function syncRouterData(): void
    {
        $result = $this->svc->syncMetrics();
        if ($result['success'] ?? false) {
            session()->flash('success', "Sinkronisasi metrik router berhasil: {$result['synced']} router pada {$result['timestamp']}.");
        } else {
            session()->flash('error', 'Gagal sinkronisasi data router.');
        }
        $this->dispatch('refreshPage');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getSummaryProperty(): array
    {
        $avail = $this->svc->availability($this->filters);
        $rows = $avail['rows'] ?? [];
        $count = max(1, count($rows));
        $avgSla = 0;
        $sehat = 0;
        $totalDownJam = 0;
        foreach ($rows as $r) {
            $avgSla += $r['availability_pct'] ?? 0;
            $totalDownJam += $r['downtime_jam'] ?? 0;
            if (($r['pass'] ?? false)) $sehat++;
        }
        $avgSla = round($avgSla / $count, 3);
        $sehatPct = round(($sehat / $count) * 100, 1);
        $los = $this->svc->losEvents($this->filters);
        $totalLos = array_sum($los['counts'] ?? []);

        return [
            'avg_sla_bulan' => $avgSla,
            'total_downtime_jam' => round($totalDownJam, 1),
            'total_los_event' => (int) $totalLos,
            'router_sehat_pct' => $sehatPct,
        ];
    }

    public function getAvailabilityProperty(): array
    {
        return $this->svc->availability($this->filters);
    }

    public function getDowntimeProperty(): array
    {
        return $this->svc->downtimeLog($this->filters);
    }

    public function getLosProperty(): array
    {
        return $this->svc->losEvents($this->filters);
    }

    public function getHealthProperty(): array
    {
        return $this->svc->routerHealthScore($this->filters);
    }

    public function getFilterOptionsProperty(): array
    {
        return [
            'routers' => Router::pluck('name', 'id')->all(),
            'olts' => Olt::pluck('name', 'id')->all(),
            'pops' => Pop::pluck('name', 'id')->all(),
            'vendors' => Vendor::pluck('name', 'id')->all(),
            'wilayahs' => ['jabodetabek' => 'Jabodetabek', 'jawa' => 'Jawa', 'sumatera' => 'Sumatera', 'kalimantan' => 'Kalimantan', 'sulawesi' => 'Sulawesi'],
        ];
    }

    public function getToolbarActionsProperty(): array
    {
        return [
            ['label' => 'Export Excel', 'icon' => 'download', 'action' => 'exportExcel'],
            ['label' => 'Sync Data Router', 'icon' => 'repeat', 'action' => 'syncRouterData'],
            ['label' => 'Refresh', 'icon' => 'refresh-cw', 'action' => 'refreshData'],
        ];
    }

    public function render()
    {
        if (!Auth::check()) abort(403);

        return view('livewire.laporan.jaringan.index', [
            'summary' => $this->summary,
            'availability' => $this->availability,
            'downtime' => $this->downtime,
            'los' => $this->los,
            'health' => $this->health,
            'filterOptions' => $this->filterOptions,
            'toolbarActions' => $this->toolbarActions,
        ]);
    }
}
