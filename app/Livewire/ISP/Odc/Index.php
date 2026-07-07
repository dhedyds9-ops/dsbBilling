<?php

namespace App\Livewire\ISP\Odc;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Odc as OdcModel;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedOdcs = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odcs';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ODC'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = OdcModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedOdcs = $query->pluck('id')->toArray();
        } else {
            $this->selectedOdcs = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedOdcs = [];
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
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            $odc = OdcModel::findOrFail($id);
            $service->delete($odc, $user);
            session()->flash('success', 'ODC berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete ODC failed', ['odc_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ODC: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            $odc = OdcModel::withTrashed()->findOrFail($id);
            $service->restore($odc, $user);
            session()->flash('success', 'ODC berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore ODC failed', ['odc_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore ODC: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            $odc = OdcModel::findOrFail($id);
            $newStatus = $odc->status === 'active' ? 'inactive' : 'active';
            $service->update($odc, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'ODC berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle ODC status failed', ['odc_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status ODC: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            $original = OdcModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'ODC berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate ODC failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi ODC: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdcs)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODC untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedOdcs, $user);
            session()->flash('success', count($this->selectedOdcs) . ' ODC berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate ODCs failed', ['odc_ids' => $this->selectedOdcs, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan ODC: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdcs)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODC untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedOdcs, $user);
            session()->flash('success', count($this->selectedOdcs) . ' ODC berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate ODCs failed', ['odc_ids' => $this->selectedOdcs, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan ODC: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedOdcs)) {
            session()->flash('error', 'Silakan pilih setidaknya satu ODC untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(OdcService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdcs)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODC untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedOdcs, $user);
            session()->flash('success', count($this->selectedOdcs) . ' ODC berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete ODCs failed', ['odc_ids' => $this->selectedOdcs, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ODC: ' . $e->getMessage());
        }
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->search = '';
        $this->showTrashed = false;
        $this->resetActionState();
        $this->resetPage();
    }

    public function render()
    {
        $query = OdcModel::with(['olt', 'pop'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $odcs = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.odc.index', compact('odcs'));
    }
}