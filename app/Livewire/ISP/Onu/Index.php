<?php

namespace App\Livewire\ISP\Onu;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Onu as OnuModel;
use App\Services\ISP\OnuService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedOnus = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'onus';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ONU'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = OnuModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('serial_number', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedOnus = $query->pluck('id')->toArray();
        } else {
            $this->selectedOnus = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedOnus = [];
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
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            $onu = OnuModel::findOrFail($id);
            $service->delete($onu, $user);
            session()->flash('success', 'ONU berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete ONU failed', ['onu_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ONU: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            $onu = OnuModel::withTrashed()->findOrFail($id);
            $service->restore($onu, $user);
            session()->flash('success', 'ONU berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore ONU failed', ['onu_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore ONU: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            $onu = OnuModel::findOrFail($id);
            $newStatus = $onu->status === 'active' ? 'inactive' : 'active';
            $service->update($onu, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'ONU berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle ONU status failed', ['onu_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status ONU: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            $original = OnuModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'ONU berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate ONU failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi ONU: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOnus)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ONU untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedOnus, $user);
            session()->flash('success', count($this->selectedOnus) . ' ONU berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate ONU failed', ['onu_ids' => $this->selectedOnus, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan ONU: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOnus)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ONU untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedOnus, $user);
            session()->flash('success', count($this->selectedOnus) . ' ONU berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate ONU failed', ['onu_ids' => $this->selectedOnus, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan ONU: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedOnus)) {
            session()->flash('error', 'Silakan pilih setidaknya satu ONU untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(OnuService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOnus)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ONU untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedOnus, $user);
            session()->flash('success', count($this->selectedOnus) . ' ONU berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete ONU failed', ['onu_ids' => $this->selectedOnus, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ONU: ' . $e->getMessage());
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
        $query = OnuModel::with(['olt', 'vendor'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('serial_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $onus = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.onu.index', compact('onus'));
    }
}
