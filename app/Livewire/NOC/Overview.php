<?php

namespace App\Livewire\NOC;

use App\Livewire\AdminComponent;
use App\Services\NOC\NocHealthService;
use App\Services\NOC\TrafficMonitoringService;
use App\Services\NOC\ServiceHealthService;
use App\Models\Alarm;
use App\Models\Provisioning\ProvisionPipeline;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class Overview extends AdminComponent
{
    // Polling interval: 30 seconds
    public int $pollingInterval = 30;

    // Filters
    public string $deviceFilter = 'all';
    public string $deviceSearch = '';
    public string $trafficPeriod = '1h';
    public string $trafficFilter = 'all';

    // UI state
    public bool $showDeviceTable = true;
    public bool $isRefreshing = false;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage = 'overview';
    }

    // -------------------------------------------------------------------------
    // Computed properties (cached per render)
    // -------------------------------------------------------------------------

    #[Computed]
    public function healthSummary(): array
    {
        return app(NocHealthService::class)->getNetworkHealthSummary();
    }

    #[Computed]
    public function headerStats(): array
    {
        return app(NocHealthService::class)->getHeaderStats();
    }

    #[Computed]
    public function serviceHealth(): array
    {
        return app(ServiceHealthService::class)->getAllServiceHealth();
    }

    #[Computed]
    public function trafficData(): array
    {
        return app(TrafficMonitoringService::class)->getTrafficSeries(
            $this->trafficPeriod,
            $this->trafficFilter,
        );
    }

    #[Computed]
    public function currentTraffic(): array
    {
        return app(TrafficMonitoringService::class)->getTotalCurrentTraffic();
    }

    #[Computed]
    public function deviceTable(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return app(NocHealthService::class)->getDeviceTable(
            $this->deviceFilter,
            $this->deviceSearch,
        );
    }

    #[Computed]
    public function activeAlarms(): \Illuminate\Database\Eloquent\Collection
    {
        $orderBy = \App\Services\Support\DbCompat::fieldOrder('level', ['critical', 'warning', 'info']);
        return Alarm::where('status', 'open')
            ->orderByRaw($orderBy)
            ->orderBy('started_at', 'desc')
            ->limit(5)
            ->get(['id', 'level', 'title', 'source_type', 'source_id', 'source_name', 'started_at', 'status']);
    }

    #[Computed]
    public function provisioningStats(): array
    {
        $counts = ProvisionPipeline::withoutTrashed()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'queued'    => $counts['pending'] ?? 0,
            'running'   => $counts['running'] ?? 0,
            'failed'    => $counts['failed'] ?? 0,
        ];
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function refreshData(): void
    {
        // Bust computed caches by unsetting
        unset($this->healthSummary);
        unset($this->headerStats);
        unset($this->serviceHealth);
        unset($this->trafficData);
        unset($this->currentTraffic);
        unset($this->deviceTable);
        unset($this->activeAlarms);
        unset($this->provisioningStats);
    }

    public function setTrafficPeriod(string $period): void
    {
        $allowed = ['15m', '1h', '6h', '24h'];
        if (in_array($period, $allowed)) {
            $this->trafficPeriod = $period;
            unset($this->trafficData);
        }
    }

    public function setDeviceFilter(string $filter): void
    {
        $allowed = ['all', 'olt', 'onu', 'router', 'pppoe'];
        if (in_array($filter, $allowed)) {
            $this->deviceFilter = $filter;
            unset($this->deviceTable);
        }
    }

    public function updatedDeviceSearch(): void
    {
        unset($this->deviceTable);
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.overview', [
            'health'       => $this->healthSummary,
            'header'       => $this->headerStats,
            'services'     => $this->serviceHealth,
            'traffic'      => $this->trafficData,
            'currentTraffic' => $this->currentTraffic,
            'devices'      => $this->deviceTable,
            'alarms'       => $this->activeAlarms,
            'provisioning' => $this->provisioningStats,
        ]);
    }
}



