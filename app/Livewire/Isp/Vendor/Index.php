<?php

namespace App\Livewire\Isp\Vendor;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\Vendor as VendorModel;
use App\Exports\VendorExport;
use App\Imports\VendorImport;
use App\Services\ISP\VendorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedVendors = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vendors';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Vendors'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = VendorModel::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

            $this->selectedVendors = $query->pluck('id')->toArray();
        } else {
            $this->selectedVendors = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedVendors = [];
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
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            $vendor = VendorModel::findOrFail($id);
            $service->delete($vendor, $user);
            session()->flash('success', 'Vendor berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete vendor failed', ['vendor_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus vendor: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            $vendor = VendorModel::withTrashed()->findOrFail($id);
            $service->restore($vendor, $user);
            session()->flash('success', 'Vendor berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore vendor failed', ['vendor_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore vendor: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            $vendor = VendorModel::findOrFail($id);
            $newStatus = $vendor->status === 'active' ? 'inactive' : 'active';
            $service->update($vendor, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'Vendor berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle vendor status failed', ['vendor_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status vendor: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            $original = VendorModel::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_copy';
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'Vendor berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate vendor failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi vendor: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedVendors)) {
                session()->flash('error', 'Silakan pilih setidaknya satu vendor untuk diaktifkan!');
                return;
            }

            $service->bulkActivate($this->selectedVendors, $user);
            session()->flash('success', count($this->selectedVendors) . ' Vendor berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk activate vendors failed', ['vendor_ids' => $this->selectedVendors, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengaktifkan vendor: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedVendors)) {
                session()->flash('error', 'Silakan pilih setidaknya satu vendor untuk dinonaktifkan!');
                return;
            }

            $service->bulkDeactivate($this->selectedVendors, $user);
            session()->flash('success', count($this->selectedVendors) . ' Vendor berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk deactivate vendors failed', ['vendor_ids' => $this->selectedVendors, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menonaktifkan vendor: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedVendors)) {
            session()->flash('error', 'Silakan pilih setidaknya satu vendor untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(VendorService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedVendors)) {
                session()->flash('error', 'Silakan pilih setidaknya satu vendor untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedVendors, $user);
            session()->flash('success', count($this->selectedVendors) . ' Vendor berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete vendors failed', ['vendor_ids' => $this->selectedVendors, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus vendor: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedVendors;
            return Excel::download(new VendorExport($selectedIds), 'vendor.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export vendor: ' . $e->getMessage());
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
            Log::info('Importing vendors', ['user_id' => auth()->id()]);

            Excel::import(new VendorImport(auth()->user()), $this->importFile);

            Log::info('Vendors imported successfully');

            session()->flash('success', 'Vendor berhasil diimpor!');
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

    public function render()
    {
        $query = VendorModel::query()
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

        $vendors = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);

        return view('livewire.isp.vendor.index', compact('vendors'));
    }
}
