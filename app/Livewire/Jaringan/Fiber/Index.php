<?php

namespace App\Livewire\Jaringan\Fiber;

use App\Livewire\BaseEnterpriseList;
use App\Services\Jaringan\FiberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'jaringan';
    public string $activePage = 'fiber';
    public string $sortField = 'id';

    public array $tabs = [
        'olt' => 'OLT',
        'onu' => 'ONU',
        'odp' => 'ODP',
        'odc' => 'ODC',
        'pop' => 'POP',
        'fiber' => 'Fiber Cable',
        'los' => 'LOS Alarm',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $losTestResult = null;
    public bool $showLosTest = false;
    public ?int $losTestOnuId = null;
    public bool $showMapPopup = false;
    public ?array $mapData = null;
    public string $bulkTab = '';

    protected FiberService $fiberService;

    public function boot(FiberService $fiberService): void
    {
        $this->fiberService = $fiberService;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'olt';
        $this->filters = [
            'pop_id' => '',
            'olt_id' => '',
            'status' => '',
            'technician_id' => '',
            'region' => '',
            'vendor_id' => '',
            'install_date_from' => '',
            'install_date_to' => '',
        ];
        $this->loadSummary();
        $this->loadFilterOptions();
    }

    public function loadSummary(): void
    {
        $this->summary = $this->fiberService->summaryCounts();
    }

    public function loadFilterOptions(): void
    {
        $this->filterOptions = [
            'pops' => $this->fiberService->getPopOptions(),
            'olts' => $this->fiberService->getOltOptions(),
            'vendors' => $this->fiberService->getVendorOptions(),
            'technicians' => $this->fiberService->getTechnicianOptions(),
            'statuses' => [
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
                'error' => 'Error',
                'los' => 'LOS',
            ],
        ];
    }

    public function getRowsQuery()
    {
        return $this->fiberService->listByTab(
            $this->activeTab,
            $this->filters,
            $this->search,
            $this->sortField,
            $this->sortDirection,
            0
        )->getQuery();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->fiberService->listByTab(
                $this->activeTab,
                $this->filters,
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage
            );
        }, 'Gagal memuat data Fiber');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 0;
        return match ($action) {
            'sync' => $this->fiberService->bulkSync($ids, $this->activeTab, $userId),
            'disable' => $this->fiberService->bulkDisable($ids, $this->activeTab, $userId),
            'enable' => $this->fiberService->bulkEnable($ids, $this->activeTab, $userId),
            'delete' => $this->fiberService->bulkDelete($ids, $this->activeTab),
            'export' => $this->doBulkExport($ids),
            default => 0,
        };
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = $this->fiberService->listByTab(
            $this->activeTab,
            $this->filters,
            $this->search,
            $this->sortField,
            $this->sortDirection,
            0
        );
        if (is_array($rows)) {
            $rows = collect($rows);
        }
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection();
        }
        $filtered = $rows->whereIn('id', $ids);
        $this->exportCsvTabDirect($filtered->all());
        return count($ids);
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $rows = $this->fiberService->listByTab(
            $this->activeTab,
            $this->filters,
            $this->search,
            $this->sortField,
            $this->sortDirection,
            0
        );
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection()->all();
        }
        return $this->fiberService->exportCsvTab($this->activeTab, $rows);
    }

    public function exportCsvTabDirect(array $rows): void
    {
        $response = $this->fiberService->exportCsvTab($this->activeTab, $rows);
        $response->send();
        exit;
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
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Sync OLT', 'icon' => 'refresh-cw', 'action' => "syncAllOLT()"],
            ['label' => 'Bulk ONU Audit', 'icon' => 'check-circle', 'action' => 'runOnuAudit()'],
            ['label' => 'Import', 'icon' => 'upload', 'action' => "dispatch('open-modal', name: 'import-modal')"],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'sync', 'label' => 'Sync'],
            ['key' => 'disable', 'label' => 'Disable'],
            ['key' => 'enable', 'label' => 'Enable'],
            ['key' => 'export', 'label' => 'Export'],
            ['key' => 'delete', 'label' => 'Delete', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'pop_id', 'label' => 'Lokasi POP', 'type' => 'select', 'options' => $opts['pops'] ?? []],
            ['key' => 'olt_id', 'label' => 'OLT', 'type' => 'select', 'options' => $opts['olts'] ?? []],
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'technician_id', 'label' => 'Teknisi', 'type' => 'select', 'options' => $opts['technicians'] ?? []],
            ['key' => 'region', 'label' => 'Wilayah', 'type' => 'text'],
            ['key' => 'vendor_id', 'label' => 'Vendor', 'type' => 'select', 'options' => $opts['vendors'] ?? []],
            ['key' => 'install_date_from', 'label' => 'Tgl Pasang (Awal)', 'type' => 'date'],
            ['key' => 'install_date_to', 'label' => 'Tgl Pasang (Akhir)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Total OLT', 'value' => $s['total_olt'] ?? 0, 'color' => 'blue', 'icon' => 'server'],
            ['label' => 'Total ONU', 'value' => $s['total_onu'] ?? 0, 'color' => 'cyan', 'icon' => 'activity'],
            ['label' => 'ONU LOS', 'value' => $s['onu_los'] ?? 0, 'color' => 'red', 'icon' => 'wifi-off'],
            ['label' => 'ODP Aktif', 'value' => $s['odp_aktif'] ?? 0, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'ODC Aktif', 'value' => $s['odc_aktif'] ?? 0, 'color' => 'purple', 'icon' => 'file-text'],
        ];
    }

    public function syncAllOLT(): void
    {
        $userId = Auth::id() ?? 0;
        $this->withLoading(function () use ($userId) {
            $count = $this->fiberService->bulkSync(\App\Models\ISP\Olt::pluck('id')->all(), 'olt', $userId);
            session()->flash('success', "Berhasil sync {$count} OLT.");
            $this->loadSummary();
            return null;
        }, 'Sync OLT gagal');
    }

    public function runOnuAudit(): void
    {
        $userId = Auth::id() ?? 0;
        $this->withLoading(function () use ($userId) {
            $result = $this->fiberService->auditOnu($userId);
            $msg = "Audit ONU: Total {$result['total']}, No SN: {$result['no_serial']}, No OLT: {$result['no_olt']}, Duplicate: {$result['duplicate']}, LOS: {$result['los']}";
            session()->flash('info', $msg);
            return null;
        }, 'Audit ONU gagal');
    }

    public function rowEdit(int $id): void
    {
        $routes = [
            'olt' => 'isp.olts.edit',
            'onu' => 'isp.onus.edit',
            'odp' => 'isp.odps.edit',
            'odc' => 'isp.odcs.edit',
            'pop' => 'isp.pops.edit',
        ];
        $route = $routes[$this->activeTab] ?? null;
        if ($route && \Illuminate\Support\Facades\Route::has($route)) {
            $this->redirect(route($route, ['id' => $id]));
        } else {
            session()->flash('info', 'Edit: ID ' . $id);
        }
    }

    public function rowDetail(int $id): void
    {
        $routes = [
            'olt' => 'isp.olts.show',
            'onu' => 'isp.onus.show',
            'odp' => 'isp.odps.show',
            'odc' => 'isp.odcs.show',
            'pop' => 'isp.pops.show',
        ];
        $route = $routes[$this->activeTab] ?? null;
        if ($route && \Illuminate\Support\Facades\Route::has($route)) {
            $this->redirect(route($route, ['id' => $id]));
        } else {
            session()->flash('info', 'Detail: ID ' . $id);
        }
    }

    public function rowSync(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->fiberService->syncDevice($id, $this->activeTab, $userId);
            session()->flash('success', 'Berhasil sync device.');
            $this->loadSummary();
        } catch (Throwable $e) {
            Log::error('rowSync failed', ['tab' => $this->activeTab, 'id' => $id, 'err' => $e->getMessage()]);
            $this->errorMessage = 'Sync gagal: ' . $e->getMessage();
        }
    }

    public function rowShowMap(int $id): void
    {
        $class = match ($this->activeTab) {
            'olt' => \App\Models\ISP\Olt::class,
            'onu' => \App\Models\ISP\Onu::class,
            'odp' => \App\Models\ISP\Odp::class,
            'odc' => \App\Models\ISP\Odc::class,
            'pop' => \App\Models\ISP\Pop::class,
            'fiber' => \App\Models\ISP\FiberCable::class,
            default => null,
        };
        if ($class) {
            $item = $class::find($id);
            if ($item) {
                $this->mapData = [
                    'id' => $item->id,
                    'name' => $item->name ?? $item->code ?? 'Device',
                    'lat' => $item->latitude ?? $item->lat ?? -6.2,
                    'lng' => $item->longitude ?? $item->lng ?? 106.8,
                ];
                $this->showMapPopup = true;
            }
        }
    }

    public function closeMapPopup(): void
    {
        $this->showMapPopup = false;
        $this->mapData = null;
    }

    public function rowTestLos(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->losTestResult = $this->fiberService->testLosOnu($id, $userId);
            $this->losTestOnuId = $id;
            $this->showLosTest = true;
            $this->loadSummary();
        } catch (Throwable $e) {
            $this->errorMessage = 'Test LOS gagal: ' . $e->getMessage();
        }
    }

    public function closeLosTest(): void
    {
        $this->showLosTest = false;
        $this->losTestResult = null;
        $this->losTestOnuId = null;
    }

    public function rowDisable(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->fiberService->disableDevice($id, $this->activeTab, $userId);
            session()->flash('success', 'Device dinonaktifkan.');
        } catch (Throwable $e) {
            $this->errorMessage = 'Disable gagal: ' . $e->getMessage();
        }
    }

    public function rowEnable(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->fiberService->enableDevice($id, $this->activeTab, $userId);
            session()->flash('success', 'Device diaktifkan.');
        } catch (Throwable $e) {
            $this->errorMessage = 'Enable gagal: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.jaringan.fiber.index', compact('rows'));
    }
}
