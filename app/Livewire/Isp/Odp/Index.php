<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\Odp as OdpModel;
use App\Exports\OdpExport;
use App\Imports\OdpImport;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedOdps = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ODP'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = OdpModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedOdps = $query->pluck('id')->toArray();
        } else {
            $this->selectedOdps = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedOdps = [];
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
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            $odp = OdpModel::findOrFail($id);
            $service->delete($odp, $user);
            session()->flash('success', 'ODP berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete ODP failed', ['odp_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ODP: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            $odp = OdpModel::withTrashed()->findOrFail($id);
            $service->restore($odp, $user);
            session()->flash('success', 'ODP berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore ODP failed', ['odp_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore ODP: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            $odp = OdpModel::findOrFail($id);
            $newStatus = $odp->status === 'active' ? 'inactive' : 'active';
            $service->update($odp, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'ODP berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle ODP status failed', ['odp_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status ODP: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            $original = OdpModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'ODP berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate ODP failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi ODP: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdps)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODP untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedOdps, $user);
            session()->flash('success', count($this->selectedOdps) . ' ODP berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate ODP failed', ['odp_ids' => $this->selectedOdps, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan ODP: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdps)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODP untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedOdps, $user);
            session()->flash('success', count($this->selectedOdps) . ' ODP berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate ODP failed', ['odp_ids' => $this->selectedOdps, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan ODP: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdps)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODP untuk direstore!');
                return;
            }

            $service->bulkRestore($this->selectedOdps, $user);
            session()->flash('success', count($this->selectedOdps) . ' ODP berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore ODP failed', ['odp_ids' => $this->selectedOdps, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore ODP: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedOdps)) {
            session()->flash('error', 'Silakan pilih setidaknya satu ODP untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(OdpService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOdps)) {
                session()->flash('error', 'Silakan pilih setidaknya satu ODP untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedOdps, $user);
            session()->flash('success', count($this->selectedOdps) . ' ODP berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete ODP failed', ['odp_ids' => $this->selectedOdps, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus ODP: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedOdps;
            return Excel::download(new OdpExport($selectedIds), 'odp.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export ODP: ' . $e->getMessage());
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
            Log::info('Importing ODPs', ['user_id' => auth()->id()]);

            Excel::import(new OdpImport(auth()->user()), $this->importFile);

            Log::info('ODPs imported successfully');

            session()->flash('success', 'ODP berhasil diimpor!');
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
        $query = OdpModel::with(['odc'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $odps = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.odp.index', compact('odps'));
    }
}
