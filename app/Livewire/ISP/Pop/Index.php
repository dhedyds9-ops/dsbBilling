<?php

namespace App\Livewire\ISP\Pop;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Pop as PopModel;
use App\Services\ISP\PopService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedPops = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'pops';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'POPs'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = PopModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('address', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedPops = $query->pluck('id')->toArray();
        } else {
            $this->selectedPops = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedPops = [];
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
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            $pop = PopModel::findOrFail($id);
            $service->delete($pop, $user);
            session()->flash('success', 'POP berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete POP failed', ['pop_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus POP: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            $pop = PopModel::withTrashed()->findOrFail($id);
            $service->restore($pop, $user);
            session()->flash('success', 'POP berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore POP failed', ['pop_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore POP: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            $pop = PopModel::findOrFail($id);
            $newStatus = $pop->status === 'active' ? 'inactive' : 'active';
            $service->update($pop, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'POP berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle POP status failed', ['pop_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status POP: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            $original = PopModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'POP berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate POP failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi POP: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedPops)) {
                session()->flash('error', 'Silakan pilih setidaknya satu POP untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedPops, $user);
            session()->flash('success', count($this->selectedPops) . ' POP berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate POPs failed', ['pop_ids' => $this->selectedPops, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan POP: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedPops)) {
                session()->flash('error', 'Silakan pilih setidaknya satu POP untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedPops, $user);
            session()->flash('success', count($this->selectedPops) . ' POP berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate POPs failed', ['pop_ids' => $this->selectedPops, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan POP: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedPops)) {
            session()->flash('error', 'Silakan pilih setidaknya satu POP untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(PopService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedPops)) {
                session()->flash('error', 'Silakan pilih setidaknya satu POP untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedPops, $user);
            session()->flash('success', count($this->selectedPops) . ' POP berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete POPs failed', ['pop_ids' => $this->selectedPops, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus POP: ' . $e->getMessage());
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
        $query = PopModel::with('tower')
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $pops = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.pop.index', compact('pops'));
    }
}