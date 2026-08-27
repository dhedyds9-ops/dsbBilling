<?php

namespace App\Livewire\ISP\Tower;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Tower as TowerModel;
use App\Exports\TowerExport;
use App\Imports\TowerImport;
use App\Services\ISP\TowerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedTowers = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'towers';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Towers'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = TowerModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('address', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedTowers = $query->pluck('id')->toArray();
        } else {
            $this->selectedTowers = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedTowers = [];
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
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            $tower = TowerModel::findOrFail($id);
            $service->delete($tower, $user);
            session()->flash('success', 'Tower berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete Tower failed', ['tower_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Tower: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            $tower = TowerModel::withTrashed()->findOrFail($id);
            $service->restore($tower, $user);
            session()->flash('success', 'Tower berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore Tower failed', ['tower_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Tower: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            $tower = TowerModel::findOrFail($id);
            $newStatus = $tower->status === 'active' ? 'inactive' : 'active';
            $service->update($tower, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'Tower berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle Tower status failed', ['tower_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status Tower: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            $original = TowerModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'Tower berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate Tower failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi Tower: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedTowers)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Tower untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedTowers, $user);
            session()->flash('success', count($this->selectedTowers) . ' Tower berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate Towers failed', ['tower_ids' => $this->selectedTowers, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan Tower: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedTowers)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Tower untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedTowers, $user);
            session()->flash('success', count($this->selectedTowers) . ' Tower berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate Towers failed', ['tower_ids' => $this->selectedTowers, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan Tower: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedTowers)) {
            session()->flash('error', 'Silakan pilih setidaknya satu Tower untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedTowers)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Tower untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedTowers, $user);
            session()->flash('success', count($this->selectedTowers) . ' Tower berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete Towers failed', ['tower_ids' => $this->selectedTowers, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Tower: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $service = app(TowerService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedTowers)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Tower untuk direstore!');
                return;
            }

            $service->bulkRestore($this->selectedTowers, $user);
            session()->flash('success', count($this->selectedTowers) . ' Tower berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore Towers failed', ['tower_ids' => $this->selectedTowers, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Tower: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedTowers;
            return Excel::download(new TowerExport($selectedIds), 'tower.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export tower: ' . $e->getMessage());
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
            Log::info('Importing towers', ['user_id' => auth()->id()]);

            Excel::import(new TowerImport(auth()->user()), $this->importFile);

            Log::info('Towers imported successfully');

            session()->flash('success', 'Tower berhasil diimpor!');
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
        $query = TowerModel::query()
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $towers = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.tower.index', compact('towers'));
    }
}