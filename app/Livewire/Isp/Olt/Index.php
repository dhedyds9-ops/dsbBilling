<?php

namespace App\Livewire\Isp\Olt;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\Olt as OltModel;
use App\Exports\OltExport;
use App\Imports\OltImport;
use App\Services\ISP\OltService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedOlts = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'olts';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'OLT'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = OltModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('model', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedOlts = $query->pluck('id')->toArray();
        } else {
            $this->selectedOlts = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedOlts = [];
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
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            $olt = OltModel::findOrFail($id);
            $service->delete($olt, $user);
            session()->flash('success', 'OLT berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete OLT failed', ['olt_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus OLT: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            $olt = OltModel::withTrashed()->findOrFail($id);
            $service->restore($olt, $user);
            session()->flash('success', 'OLT berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore OLT failed', ['olt_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore OLT: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            $olt = OltModel::findOrFail($id);
            $newStatus = $olt->status === 'active' ? 'inactive' : 'active';
            $service->update($olt, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'OLT berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle OLT status failed', ['olt_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status OLT: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            $original = OltModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'OLT berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate OLT failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi OLT: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOlts)) {
                session()->flash('error', 'Silakan pilih setidaknya satu OLT untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedOlts, $user);
            session()->flash('success', count($this->selectedOlts) . ' OLT berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate OLT failed', ['olt_ids' => $this->selectedOlts, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan OLT: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOlts)) {
                session()->flash('error', 'Silakan pilih setidaknya satu OLT untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedOlts, $user);
            session()->flash('success', count($this->selectedOlts) . ' OLT berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate OLT failed', ['olt_ids' => $this->selectedOlts, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan OLT: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedOlts)) {
            session()->flash('error', 'Silakan pilih setidaknya satu OLT untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOlts)) {
                session()->flash('error', 'Silakan pilih setidaknya satu OLT untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedOlts, $user);
            session()->flash('success', count($this->selectedOlts) . ' OLT berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete OLT failed', ['olt_ids' => $this->selectedOlts, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus OLT: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedOlts;
            return Excel::download(new OltExport($selectedIds), 'olt.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export OLT: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $service = app(OltService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedOlts)) {
                session()->flash('error', 'Silakan pilih setidaknya satu OLT untuk direstore!');
                return;
            }

            $service->bulkRestore($this->selectedOlts, $user);
            session()->flash('success', count($this->selectedOlts) . ' OLT berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore OLT failed', ['olt_ids' => $this->selectedOlts, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore OLT: ' . $e->getMessage());
        }
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
            Log::info('Importing OLTs', ['user_id' => auth()->id()]);

            Excel::import(new OltImport(auth()->user()), $this->importFile);

            Log::info('OLTs imported successfully');

            session()->flash('success', 'OLT berhasil diimpor!');
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

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->search = '';
        $this->showTrashed = false;
        $this->resetActionState();
        $this->resetPage();
    }

    public function rowSync($id)
    {
        Log::info(__METHOD__);
        try {
            $olt = OltModel::findOrFail($id);
            $service = app(\App\Services\ISP\OltPollingService::class);
            $result = $service->pollOlt($olt);
            
            if ($result['success'] ?? false) {
                $msg = 'Berhasil sync OLT. ONU Online: ' . ($result['onu_online'] ?? 0);
                session()->flash('success', $msg);
                $this->dispatch('toast', type: 'success', message: $msg);
            } else {
                $err = $result['error'] ?? 'Gagal menghubungi OLT (SNMP)';
                session()->flash('error', 'Sync gagal: ' . $err);
                $this->dispatch('toast', type: 'error', message: 'Sync gagal: ' . $err);
            }
        } catch (Throwable $e) {
            Log::error('rowSync failed', ['id' => $id, 'err' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', message: 'Sync error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = OltModel::with(['pop', 'vendor'])
            ->withCount(['onus', 'onus as onus_online_count' => function ($q) {
                $q->where('status', 'active');
            }, 'onus as onus_offline_count' => function ($q) {
                $q->where('status', 'inactive');
            }])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('model', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $query = $query->orderBy($this->sortField, $this->sortDirection);
        $olts = $this->perPage === 'All' ? $query->get() : $query->paginate($this->perPage);

        return view('livewire.isp.olt.index', compact('olts'));
    }
}
