<?php

namespace App\Livewire\ISP\HotspotUser;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\HotspotUser;
use App\Exports\HotspotUserExport;
use App\Imports\HotspotUserImport;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Throwable;

class Index extends BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Hotspot Users'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = HotspotUser::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('username', 'like', '%' . $this->search . '%')
                            ->orWhereHas('customer', function($cq) {
                                $cq->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('phone', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->when($this->filters['status'], function($q) {
                    $q->where('status', $this->filters['status']);
                });

            $this->selectedIds = $query->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedIds = [];
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
        $service = app(HotspotService::class);
        $user = Auth::user();

        try {
            $hotspotUser = HotspotUser::findOrFail($id);
            $service->terminateHotspotUser($hotspotUser->id, $user->id);
            $hotspotUser->delete();
            session()->flash('success', 'Hotspot User berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete Hotspot User failed', ['hotspot_user_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Hotspot User: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $user = Auth::user();

        try {
            $hotspotUser = HotspotUser::withTrashed()->findOrFail($id);
            $hotspotUser->restore();
            $hotspotUser->update(['updated_by' => $user->id]);
            session()->flash('success', 'Hotspot User berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore Hotspot User failed', ['hotspot_user_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Hotspot User: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        Log::info(__METHOD__);
        $service = app(HotspotService::class);
        $user = Auth::user();

        try {
            $hotspotUser = HotspotUser::findOrFail($id);
            if ($hotspotUser->status === 'active') {
                $service->suspendHotspotUser($hotspotUser->id, $user->id);
            } else if ($hotspotUser->status === 'suspended') {
                $service->activateHotspotUser($hotspotUser->id, $user->id);
            }
            session()->flash('success', 'Status Hotspot User berhasil diubah!');
        } catch (Throwable $e) {
            Log::error('Toggle Hotspot User status failed', ['hotspot_user_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status Hotspot User: ' . $e->getMessage());
        }
    }

    public function renew($id, \App\Services\Billing\InvoiceService $invoiceService)
    {
        $hotspotUser = HotspotUser::with('customerService.contract')->findOrFail($id);
        
        if (!$hotspotUser->customerService || !$hotspotUser->customerService->contract) {
            session()->flash('error', 'Hotspot User tidak memiliki kontrak!');
            return;
        }

        $contract = $hotspotUser->customerService->contract;
        $serviceProfile = $hotspotUser->serviceProfile;

        $invoiceService->createInvoice(
            $contract,
            Auth::id(),
            [
                [
                    'description' => 'Renew Langganan ' . ($serviceProfile->name ?? 'Paket'),
                    'quantity' => 1,
                    'unit_price' => $hotspotUser->subscription?->recurring_price ?? 0,
                ],
            ],
        );

        session()->flash('success', 'Invoice untuk renew berhasil dibuat!');
    }

    public function print($id)
    {
        $hotspotUser = HotspotUser::with(['customer', 'serviceProfile', 'subscription'])->findOrFail($id);
        
        session()->flash('info', 'Fitur print sedang dalam pengembangan! Data user: ' . $hotspotUser->username);
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Silakan pilih setidaknya satu Hotspot User untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(HotspotService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Hotspot User untuk dihapus!');
                return;
            }

            foreach ($this->selectedIds as $id) {
                $hotspotUser = HotspotUser::findOrFail($id);
                $service->terminateHotspotUser($hotspotUser->id, $user->id);
                $hotspotUser->delete();
            }
            
            session()->flash('success', count($this->selectedIds) . ' Hotspot User berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete Hotspot User failed', ['hotspot_user_ids' => $this->selectedIds, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Hotspot User: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Hotspot User untuk direstore!');
                return;
            }

            foreach ($this->selectedIds as $id) {
                $hotspotUser = HotspotUser::withTrashed()->findOrFail($id);
                $hotspotUser->restore();
                $hotspotUser->update(['updated_by' => $user->id]);
            }
            
            session()->flash('success', count($this->selectedIds) . ' Hotspot User berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore Hotspot User failed', ['hotspot_user_ids' => $this->selectedIds, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Hotspot User: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedIds;
            return Excel::download(new HotspotUserExport($selectedIds), 'hotspot-users.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export hotspot users: ' . $e->getMessage());
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
            Log::info('Importing hotspot users', ['user_id' => auth()->id()]);

            Excel::import(new HotspotUserImport(auth()->user()), $this->importFile);

            Log::info('Hotspot users imported successfully');

            session()->flash('success', 'Hotspot User berhasil diimpor!');
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
        $query = HotspotUser::with(['customer', 'serviceProfile', 'subscription', 'createdBy'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('username', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filters['status'], function($q) {
                $q->where('status', $this->filters['status']);
            });

        $query = $query->orderBy($this->sortField, $this->sortDirection);
        $hotspotUsers = $this->perPage === 'All' ? $query->get() : $query->paginate($this->perPage);

        $stats = [
            'total' => HotspotUser::count(),
            'active' => HotspotUser::where('status', 'active')->count(),
            'inactive' => HotspotUser::where('status', 'inactive')->count(),
            'online' => HotspotUser::where('status', 'active')->where('is_online', true)->count(),
        ];

        return view('livewire.isp.hotspot-user.index', compact('hotspotUsers', 'stats'));
    }
}
