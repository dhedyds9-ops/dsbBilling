<?php

namespace App\Livewire\ISP\ServiceProfile;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\ServiceProfile as ServiceProfileModel;
use App\Models\ISP\ServiceProfileType;
use App\Exports\ServiceProfileExport;
use App\Imports\ServiceProfileImport;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;
    
    public ?int $typeId = null;
    public bool $showTrashed = false;
    public array $selectedPackages = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;
    public bool $showBulkEditModal = false;
    public $bulkEditData = [
        'download_speed' => null,
        'upload_speed' => null,
        'base_price' => null,
        'owner_price' => null,
        'reseller_price' => null,
        'owner_id' => null,
        'status' => null,
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'service-profiles';
        $this->filters = ['status' => '', 'service_type' => '', 'owner_id' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket Internet'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = ServiceProfileModel::with(['serviceProfileType', 'owner'])
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('download_speed', 'like', '%' . $this->search . '%')
                          ->orWhere('upload_speed', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], function($q) {
                    $q->where('status', $this->filters['status']);
                })
                ->when($this->filters['service_type'], function($q) {
                    $q->where('service_type', $this->filters['service_type']);
                })
                ->when(isset($this->filters['owner_id']) && $this->filters['owner_id'], function($q) {
                    $q->where('owner_id', $this->filters['owner_id']);
                })
                ->when($this->typeId, function($q) {
                    $q->where('service_profile_type_id', $this->typeId);
                });

            $this->selectedPackages = $query->pluck('id')->toArray();
        } else {
            $this->selectedPackages = [];
        }
    }

    /**
     * Reset action state
     */
    private function resetActionState()
    {
        $this->selectedPackages = [];
        $this->selectAll = false;
        $this->showDeleteModal = false;
    }

    /**
     * Close delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    /**
     * Delete single package
     */
    public function delete($id)
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $profile = ServiceProfileModel::findOrFail($id);
            
            Log::info('Delete Package', [
                'package_id' => $id,
                'package_code' => $profile->code,
                'package_name' => $profile->name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'owner_id' => $profile->owner_id,
                'time' => now()->toDateTimeString(),
            ]);
            
            $service->deleteProfile($profile, $user);
            
            Log::info('Delete Package Success', [
                'package_id' => $id,
                'user_id' => $user->id,
            ]);
            
            session()->flash('success', 'Paket Internet berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete Package Failed', [
                'package_id' => $id,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }

    /**
     * Restore single package
     */
    public function restore($id)
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $profile = ServiceProfileModel::withTrashed()->findOrFail($id);
            
            Log::info('Restore Package', [
                'package_id' => $id,
                'package_code' => $profile->code,
                'package_name' => $profile->name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'owner_id' => $profile->owner_id,
                'time' => now()->toDateTimeString(),
            ]);
            
            $service->restoreProfile($profile, $user);
            
            Log::info('Restore Package Success', [
                'package_id' => $id,
                'user_id' => $user->id,
            ]);
            
            session()->flash('success', 'Paket Internet berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore Package Failed', [
                'package_id' => $id,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal merestore paket: ' . $e->getMessage());
        }
    }

    /**
     * Toggle package status
     */
    public function toggleStatus($id)
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $profile = ServiceProfileModel::findOrFail($id);
            $newStatus = $profile->status === 'active' ? 'inactive' : 'active';
            
            Log::info('Toggle Package Status', [
                'package_id' => $id,
                'package_code' => $profile->code,
                'package_name' => $profile->name,
                'old_status' => $profile->status,
                'new_status' => $newStatus,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'owner_id' => $profile->owner_id,
                'time' => now()->toDateTimeString(),
            ]);
            
            $service->updateProfile($profile, ['status' => $newStatus], $user);
            
            Log::info('Toggle Package Status Success', [
                'package_id' => $id,
                'new_status' => $newStatus,
                'user_id' => $user->id,
            ]);
            
            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'Paket Internet berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle Package Status Failed', [
                'package_id' => $id,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal mengubah status paket: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate package
     */
    public function duplicate($id)
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $original = ServiceProfileModel::findOrFail($id);
            
            Log::info('Duplicate Package', [
                'original_id' => $id,
                'original_code' => $original->code,
                'original_name' => $original->name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'owner_id' => $original->owner_id,
                'time' => now()->toDateTimeString(),
            ]);
            
            $newProfile = $service->cloneProfile($original, $user, ['status' => 'inactive']);
            
            Log::info('Duplicate Package Success', [
                'original_id' => $id,
                'new_id' => $newProfile->id,
                'new_code' => $newProfile->code,
                'new_name' => $newProfile->name,
                'user_id' => $user->id,
            ]);
            
            session()->flash('success', 'Paket Internet berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate Package Failed', [
                'original_id' => $id,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal menduplikasi paket: ' . $e->getMessage());
        }
    }

    /**
     * Bulk activate packages
     */
    public function bulkActivate()
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            if (empty($this->selectedPackages)) {
                session()->flash('error', 'Silakan pilih setidaknya satu paket untuk diaktifkan!');
                return;
            }
            
            Log::info('Bulk Activate Packages', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'time' => now()->toDateTimeString(),
            ]);
            
            $service->bulkActivate($this->selectedPackages, $user);
            
            Log::info('Bulk Activate Packages Success', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
            ]);
            
            session()->flash('success', count($this->selectedPackages) . ' Paket Internet berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk Activate Packages Failed', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal mengaktifkan paket: ' . $e->getMessage());
        }
    }

    /**
     * Bulk deactivate packages
     */
    public function bulkDeactivate()
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            if (empty($this->selectedPackages)) {
                session()->flash('error', 'Silakan pilih setidaknya satu paket untuk dinonaktifkan!');
                return;
            }
            
            Log::info('Bulk Deactivate Packages', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'time' => now()->toDateTimeString(),
            ]);
            
            $service->bulkDeactivate($this->selectedPackages, $user);
            
            Log::info('Bulk Deactivate Packages Success', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
            ]);
            
            session()->flash('success', count($this->selectedPackages) . ' Paket Internet berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk Deactivate Packages Failed', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal menonaktifkan paket: ' . $e->getMessage());
        }
    }

    /**
     * Confirm bulk delete
     */
    public function confirmBulkDelete()
    {
        if (empty($this->selectedPackages)) {
            session()->flash('error', 'Silakan pilih setidaknya satu paket untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }
    
    /**
     * Confirm bulk activate
     */
    public function confirmBulkActivate()
    {
        if (empty($this->selectedPackages)) {
            session()->flash('error', 'Silakan pilih setidaknya satu paket untuk diaktifkan!');
            return;
        }
        $this->bulkActivate();
    }
    
    /**
     * Confirm bulk deactivate
     */
    public function confirmBulkDeactivate()
    {
        if (empty($this->selectedPackages)) {
            session()->flash('error', 'Silakan pilih setidaknya satu paket untuk dinonaktifkan!');
            return;
        }
        $this->bulkDeactivate();
    }

    /**
     * Bulk delete packages
     */
    public function bulkDelete()
    {
        \Log::info(__METHOD__);
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            if (empty($this->selectedPackages)) {
                session()->flash('error', 'Silakan pilih setidaknya satu paket untuk dihapus!');
                return;
            }
            
            Log::info('Bulk Delete Packages', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id ?? null,
                'time' => now()->toDateTimeString(),
            ]);
            
            $result = $service->bulkDelete($this->selectedPackages, $user);
            
            Log::info('Bulk Delete Packages Completed', [
                'success_count' => $result['success'],
                'failed_count' => $result['failed'],
                'user_id' => $user->id,
            ]);
            
            if ($result['failed'] > 0) {
                $message = $result['success'] . ' Paket berhasil dihapus. ' . $result['failed'] . ' Paket gagal karena masih digunakan pelanggan.';
                session()->flash('warning', $message);
            } else {
                session()->flash('success', $result['success'] . ' Paket berhasil dihapus.');
            }
            
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk Delete Packages Failed', [
                'package_ids' => $this->selectedPackages,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            session()->flash('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }

    public function export()
    {
        \Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedPackages;
            return Excel::download(new ServiceProfileExport($selectedIds), 'paket-internet.xlsx');
        } catch (Throwable $e) {
            \Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export paket: ' . $e->getMessage());
        }
    }

    public function print()
    {
        \Log::info(__METHOD__);
        try {
            $query = ServiceProfileModel::with(['owner', 'serviceProfileType']);

            if (!empty($this->selectedPackages)) {
                $query->whereIn('id', $this->selectedPackages);
            }

            $profiles = $query->get();

            $pdf = Pdf::loadView('pdf.service-profiles', compact('profiles'));
            return $pdf->download('daftar-paket-internet.pdf');
        } catch (Throwable $e) {
            \Log::error('Print Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal cetak daftar paket: ' . $e->getMessage());
        }
    }

    public function resetFilters()
    {
        $this->filters = ['status' => '', 'service_type' => '', 'owner_id' => ''];
        $this->typeId = null;
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
            Log::info('Importing service profiles', ['user_id' => auth()->id()]);
            
            Excel::import(new ServiceProfileImport(auth()->user()), $this->importFile);
            
            Log::info('Service profiles imported successfully');
            
            session()->flash('success', 'Paket berhasil diimpor!');
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
    
    public function openBulkEditModal()
    {
        if (empty($this->selectedPackages)) {
            session()->flash('error', 'Silakan pilih setidaknya satu paket!');
            return;
        }
        
        $this->bulkEditData = [
            'download_speed' => null,
            'upload_speed' => null,
            'base_price' => null,
            'owner_price' => null,
            'reseller_price' => null,
            'owner_id' => null,
            'status' => null,
        ];
        $this->showBulkEditModal = true;
    }
    
    public function closeBulkEditModal()
    {
        $this->showBulkEditModal = false;
        $this->bulkEditData = [
            'download_speed' => null,
            'upload_speed' => null,
            'base_price' => null,
            'owner_price' => null,
            'reseller_price' => null,
            'owner_id' => null,
            'status' => null,
        ];
    }
    
    public function bulkEdit()
    {
        if (empty($this->selectedPackages)) {
            session()->flash('error', 'Silakan pilih setidaknya satu paket!');
            return;
        }
        
        $service = app(ServiceProfileService::class);
        $user = auth()->user();
        
        try {
            $result = $service->bulkEdit($this->selectedPackages, $this->bulkEditData, $user);
            
            if ($result['failed'] > 0) {
                session()->flash('warning', "{$result['updated']} paket berhasil diperbarui, {$result['failed']} gagal.");
            } else {
                session()->flash('success', "{$result['updated']} paket berhasil diperbarui!");
            }
            
            $this->closeBulkEditModal();
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk edit failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal memperbarui paket: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $types = ServiceProfileType::all();

        $query = ServiceProfileModel::with(['serviceProfileType', 'owner'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('download_speed', 'like', '%' . $this->search . '%')
                      ->orWhere('upload_speed', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], function($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['service_type'], function($q) {
                $q->where('service_type', $this->filters['service_type']);
            })
            ->when(isset($this->filters['owner_id']) && $this->filters['owner_id'], function($q) {
                $q->where('owner_id', $this->filters['owner_id']);
            })
            ->when($this->typeId, function($q) {
                $q->where('service_profile_type_id', $this->typeId);
            });

        $profiles = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.isp.service-profiles.index', compact('profiles', 'types'));
    }
}
