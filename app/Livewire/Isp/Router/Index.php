<?php

namespace App\Livewire\Isp\Router;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\Router as RouterModel;
use App\Exports\RouterExport;
use App\Imports\RouterImport;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedRouters = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public bool $showScriptModal = false;
    public ?RouterModel $scriptRouter = null;

    // Provisioning modal state
    public $provisioningToken = null;
    public $provisioningExpires = null;
    public bool $showProvisioningModal = false;

    public function openScriptModal($id)
    {
        $this->generateProvisioningToken($id);
    }

    public function closeScriptModal()
    {
        $this->showScriptModal = false;
        $this->scriptRouter = null;
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router List'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = RouterModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('model', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'] ?? null, fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedRouters = $query->pluck('id')->toArray();
        } else {
            $this->selectedRouters = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedRouters = [];
        $this->selectAll = false;
        $this->showDeleteModal = false;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    public function delete($id)
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            $router = RouterModel::findOrFail($id);
            $service->delete($router, $user);
            session()->flash('success', 'Router berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete Router failed', ['router_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Router: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            $router = RouterModel::withTrashed()->findOrFail($id);
            $service->restore($router, $user);
            session()->flash('success', 'Router berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore Router failed', ['router_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Router: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            $router = RouterModel::findOrFail($id);
            $newStatus = $router->status === 'active' ? 'inactive' : 'active';
            $service->update($router, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'Router berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle Router status failed', ['router_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status Router: ' . $e->getMessage());
        }
    }

    public function generateProvisioningToken($id)
    {
        try {
            $router = RouterModel::findOrFail($id);
            $service = app(\App\Services\Provisioning\RouterProvisioningService::class);
            $session = $service->generateSession($router, auth()->id());

            $this->provisioningToken = $session->raw_token;
            $this->provisioningExpires = $session->expires_at->diffForHumans();
            $this->showProvisioningModal = true;
        } catch (Throwable $e) {
            Log::error('Generate Provisioning Token failed', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal generate provisioning token: ' . $e->getMessage());
        }
    }

    public function closeProvisioningModal()
    {
        $this->showProvisioningModal = false;
        $this->provisioningToken = null;
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            $original = RouterModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'Router berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate Router failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi Router: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedRouters)) {
            session()->flash('error', 'Silakan pilih setidaknya satu Router untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedRouters)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Router untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedRouters, $user);
            session()->flash('success', count($this->selectedRouters) . ' Router berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete Router failed', ['router_ids' => $this->selectedRouters, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Router: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $service = app(RouterService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedRouters)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Router untuk direstore!');
                return;
            }

            $service->bulkRestore($this->selectedRouters, $user);
            session()->flash('success', count($this->selectedRouters) . ' Router berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore Router failed', ['router_ids' => $this->selectedRouters, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Router: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedRouters;
            return Excel::download(new RouterExport($selectedIds), 'router-mikrotik.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export router: ' . $e->getMessage());
        }
    }

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->search = '';
        $this->showTrashed = false;
        $this->resetActionState();
        $this->resetPage();
    }

    public function openImportModal()
    {
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Log::info('Importing routers', ['user_id' => auth()->id()]);
            Excel::import(new RouterImport(auth()->user()), $this->importFile);
            Log::info('Routers imported successfully');
            session()->flash('success', 'Router berhasil diimpor!');
            $this->closeImportModal();
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            session()->flash('error', 'Gagal mengimpor: ' . implode('; ', $errors));
        } catch (Throwable $e) {
            Log::error('Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal mengimpor: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = RouterModel::with(['pop', 'vendor'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('model', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'] ?? null, fn($q) => $q->where('status', $this->filters['status']));

        $routers = $query->orderBy($this->sortField, $this->sortDirection)
                          ->paginate($this->perPage);

        // Pre-compute online status dari cache — protected from any blocking/exception
        $monitoringService = app(\App\Services\ISP\MonitoringService::class);
        $onlineStatus = [];
        $activeUsers = [];
        foreach ($routers as $router) {
            try {
                $onlineStatus[$router->id] = $monitoringService->ping($router);
                
                // Ambil jumlah user aktif dari cache (PPP + Hotspot)
                $ppp = $monitoringService->getPPPActive($router);
                $hotspot = $monitoringService->getHotspotActive($router);
                $activeUsers[$router->id] = count($ppp) + count($hotspot);
            } catch (Throwable $e) {
                $onlineStatus[$router->id] = false;
                $activeUsers[$router->id] = 0;
            }
        }

        $summary = [
            'total' => RouterModel::count(),
            'active' => RouterModel::where('status', 'active')->count(),
            'inactive' => RouterModel::where('status', 'inactive')->count(),
        ];

        return view('livewire.isp.router.index', compact('routers', 'onlineStatus', 'activeUsers', 'summary'));
    }
}
