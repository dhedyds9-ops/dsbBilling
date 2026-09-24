<?php

namespace App\Livewire\Jaringan\Monitoring;

use App\Livewire\BaseEnterpriseList;
use App\Services\Jaringan\MonitoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'jaringan';
    public string $activePage = 'monitoring';
    public string $sortField = 'id';

    public array $tabs = [
        'realtime' => 'Realtime',
        'pppoe' => 'PPPoE Online',
        'hotspot' => 'Hotspot Online',
        'bandwidth' => 'Bandwidth Usage',
        'resources' => 'System Resources',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $recentAlarms = null;
    public ?array $bandwidthData = null;
    public ?array $resourcesData = null;
    public bool $showKillConfirm = false;

    protected MonitoringService $monitoringService;

    public function boot(MonitoringService $monitoringService): void
    {
        $this->monitoringService = $monitoringService;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'realtime';
        $this->filters = [
            'router_id' => '',
            'interface' => '',
            'type' => '',
            'customer_id' => '',
            'status' => '',
            'last_seen_from' => '',
            'last_seen_to' => '',
        ];
        $this->loadAll();
    }

    public function loadAll(): void
    {
        $this->summary = $this->monitoringService->summaryCounts();
        $this->filterOptions = [
            'routers' => $this->monitoringService->getRouterOptions(),
            'customers' => $this->monitoringService->getCustomerOptions(),
            'types' => ['pppoe' => 'PPPoE', 'hotspot' => 'Hotspot', 'voucher' => 'Voucher'],
            'statuses' => ['online' => 'Online', 'idle' => 'Idle', 'expired' => 'Expired'],
        ];
        $this->recentAlarms = $this->monitoringService->recentAlarms(12);
        $this->bandwidthData = $this->monitoringService->bandwidthPerRouter(null);
        $this->resourcesData = $this->monitoringService->systemResources();
    }

    public function getRowsQuery()
    {
        $s = $this->monitoringService;
        if ($this->activeTab === 'pppoe') {
            return $s->pppoeOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, 0)->getQuery();
        }
        if ($this->activeTab === 'hotspot') {
            return $s->hotspotOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, 0)->getQuery();
        }
        return \App\Models\ISP\Router::query();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $s = $this->monitoringService;
            if ($this->activeTab === 'pppoe') {
                return $s->pppoeOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, $this->perPage);
            }
            if ($this->activeTab === 'hotspot') {
                return $s->hotspotOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, $this->perPage);
            }
            return collect([]);
        }, 'Gagal memuat monitoring');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 0;
        if ($action === 'kick') {
            $type = $this->activeTab === 'hotspot' ? 'hotspot' : 'pppoe';
            return $this->monitoringService->bulkKick($ids, $type, $userId);
        }
        if ($action === 'export') {
            return $this->doBulkExport($ids);
        }
        return 0;
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = $this->getRowsForExport();
        if ($this->activeTab === 'bandwidth') {
            $response = $this->monitoringService->exportCsvTab('bandwidth', $this->bandwidthData ?? []);
            $response->send();
            exit;
        }
        if ($this->activeTab === 'resources') {
            $response = $this->monitoringService->exportCsvTab('resources', $this->resourcesData ?? []);
            $response->send();
            exit;
        }
        $filtered = collect($rows)->whereIn('id', $ids)->all();
        $response = $this->monitoringService->exportCsvTab($this->activeTab, $filtered);
        $response->send();
        exit;
        return count($ids);
    }

    protected function getRowsForExport()
    {
        $s = $this->monitoringService;
        if ($this->activeTab === 'pppoe') {
            $rows = $s->pppoeOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        } elseif ($this->activeTab === 'hotspot') {
            $rows = $s->hotspotOnline($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        } else {
            $rows = [];
        }
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection()->all();
        }
        return $rows;
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if ($this->activeTab === 'bandwidth') {
            return $this->monitoringService->exportCsvTab('bandwidth', $this->bandwidthData ?? []);
        }
        if ($this->activeTab === 'resources') {
            return $this->monitoringService->exportCsvTab('resources', $this->resourcesData ?? []);
        }
        $rows = $this->getRowsForExport();
        return $this->monitoringService->exportCsvTab($this->activeTab, $rows);
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
        }
    }

    public function getToolbarActions(): array
    {
        return [
            ['label' => 'Refresh', 'icon' => 'refresh-cw', 'action' => 'refreshAll()'],
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Ping All', 'icon' => 'play', 'action' => 'pingAll()'],
            ['label' => 'Kill All Sessions', 'icon' => 'alert-triangle', 'action' => "confirmKillAll()"],
        ];
    }

    public function getBulkActions(): array
    {
        if (in_array($this->activeTab, ['pppoe', 'hotspot'])) {
            return [
                ['key' => 'kick', 'label' => 'Kick Session', 'variant' => 'bg-amber-600 text-white hover:bg-amber-700'],
                ['key' => 'export', 'label' => 'Export'],
            ];
        }
        return [
            ['key' => 'export', 'label' => 'Export'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'router_id', 'label' => 'Router', 'type' => 'select', 'options' => $opts['routers'] ?? []],
            ['key' => 'interface', 'label' => 'Interface', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Tipe', 'type' => 'select', 'options' => $opts['types'] ?? []],
            ['key' => 'customer_id', 'label' => 'Pelanggan', 'type' => 'select', 'options' => $opts['customers'] ?? []],
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'last_seen_from', 'label' => 'Last Seen (Awal)', 'type' => 'date'],
            ['key' => 'last_seen_to', 'label' => 'Last Seen (Akhir)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Router Up', 'value' => $s['router_up'] ?? 0, 'color' => 'green', 'icon' => 'server'],
            ['label' => 'Router Down', 'value' => $s['router_down'] ?? 0, 'color' => 'red', 'icon' => 'wifi-off'],
            ['label' => 'PPPoE Online', 'value' => $s['pppoe_online'] ?? 0, 'color' => 'blue', 'icon' => 'users'],
            ['label' => 'Hotspot Online', 'value' => $s['hotspot_online'] ?? 0, 'color' => 'purple', 'icon' => 'wifi'],
            ['label' => 'Total Bandwidth', 'value' => ($s['total_bandwidth_mbps'] ?? 0) . ' Mbps', 'color' => 'cyan', 'icon' => 'activity'],
            ['label' => 'Alarms Active', 'value' => $s['alarms_active'] ?? 0, 'color' => 'amber', 'icon' => 'alert-triangle'],
            ['label' => 'Ticket Open', 'value' => $s['ticket_open'] ?? 0, 'color' => 'slate', 'icon' => 'ticket'],
        ];
    }

    public function refreshAll(): void
    {
        $this->withLoading(function () {
            $this->loadAll();
            session()->flash('success', 'Data monitoring diperbarui.');
            return null;
        }, 'Refresh gagal');
    }

    public function pingAll(): void
    {
        $userId = Auth::id() ?? 0;
        $this->withLoading(function () use ($userId) {
            $r = $this->monitoringService->pingAllRouters($userId);
            session()->flash('success', "Ping: {$r['up']} UP / {$r['down']} DOWN");
            $this->loadAll();
            return null;
        }, 'Ping gagal');
    }

    public function confirmKillAll(): void
    {
        $this->confirmTitle = 'Kill All Sessions';
        $this->confirmMessage = 'Anda akan memutus SEMUA sesi PPPoE dan Hotspot yang aktif. Tindakan ini tidak bisa dibatalkan. Lanjutkan?';
        $this->confirmAction = 'kill-all';
        $this->confirmBtnText = 'Kill All';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: 'confirm');
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'kill-all') {
            $userId = Auth::id() ?? 0;
            try {
                $r = $this->monitoringService->killAllSessions($userId);
                session()->flash('success', "Killed {$r['total']} sessions (PPPoE: {$r['pppoe']}, Hotspot: {$r['hotspot']})");
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Kill all gagal: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: 'confirm');
            $this->confirmAction = '';
            return;
        }
        parent::handleConfirm();
    }

    public function kickSession(string $sessionId): void
    {
        $userId = Auth::id() ?? 0;
        $type = $this->activeTab === 'hotspot' ? 'hotspot' : 'pppoe';
        try {
            $this->monitoringService->kickSession($sessionId, $type, $userId);
            session()->flash('success', 'Session dihentikan.');
        } catch (Throwable $e) {
            $this->errorMessage = 'Kick gagal: ' . $e->getMessage();
        }
    }

    public function refreshRouter(int $routerId): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->resourcesData = $this->monitoringService->refreshRouterResources($routerId, $userId);
            session()->flash('success', 'Resources router diperbarui.');
        } catch (Throwable $e) {
            $this->errorMessage = 'Refresh gagal: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.jaringan.monitoring.index', compact('rows'));
    }
}
