<?php

namespace App\Livewire\Isp\PPPoEUser;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\PPPoEUser;
use App\Services\ISP\PPPoEService;
use App\Exports\PPPoEUserExport;
use App\Imports\PPPoEUserImport;
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
        $this->activePage = 'pppoe-users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'PPPoE Users'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = PPPoEUser::query()
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
                ->when(!empty($this->filters['status']), function($q) {
                if ($this->filters['status'] === 'online') {
                    $q->where('status', 'active')->whereHas('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } elseif ($this->filters['status'] === 'offline') {
                    $q->where('status', 'active')->whereDoesntHave('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } else {
                    $q->where('status', $this->filters['status']);
                }
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

    public function delete($id, PPPoEService $service)
    {
        Log::info(__METHOD__);
        $pppoeUser = PPPoEUser::findOrFail($id);
        $service->terminatePPPoEUser($pppoeUser->id, Auth::id());
        $pppoeUser->delete();
        session()->flash('success', 'PPPoE User berhasil dihapus!');
        $this->resetActionState();
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        try {
            $pppoeUser = PPPoEUser::withTrashed()->findOrFail($id);
            $pppoeUser->restore();
            $pppoeUser->update(['updated_by' => Auth::id()]);
            session()->flash('success', 'PPPoE User berhasil dipulihkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore PPPoE User failed', ['pppoe_user_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal memulihkan PPPoE User: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id, PPPoEService $service)
    {
        Log::info(__METHOD__);
        $pppoeUser = PPPoEUser::findOrFail($id);
        if ($pppoeUser->status === 'active') {
            $service->suspendPPPoEUser($pppoeUser->id, Auth::id());
            $name = $pppoeUser->customer->name ?? $pppoeUser->username;
            session()->flash('success', "Akses PPPoE untuk pelanggan {$name} berhasil di-suspend / diisolir!");
        } else if ($pppoeUser->status === 'suspended') {
            $service->reactivatePPPoEUser($pppoeUser->id, Auth::id());
            $name = $pppoeUser->customer->name ?? $pppoeUser->username;
            session()->flash('success', "Akses PPPoE untuk pelanggan {$name} berhasil diaktifkan kembali!");
        }
    }

    public function renew($id, \App\Services\Billing\InvoiceService $invoiceService)
    {
        $pppoeUser = PPPoEUser::with('customerService.contract')->findOrFail($id);
        
        if (!$pppoeUser->customerService || !$pppoeUser->customerService->contract) {
            session()->flash('error', 'PPPoE User tidak memiliki kontrak!');
            return;
        }

        $contract = $pppoeUser->customerService->contract;
        $serviceProfile = $pppoeUser->serviceProfile;

        $invoiceService->createInvoice(
            $contract,
            Auth::id(),
            [
                [
                    'description' => 'Renew Langganan ' . ($serviceProfile->name ?? 'Paket'),
                    'quantity' => 1,
                    'unit_price' => $pppoeUser->subscription?->recurring_price ?? 0,
                ],
            ],
        );

        session()->flash('success', 'Invoice untuk renew berhasil dibuat!');
    }

    public function printUser($id)
    {
        $pppoeUser = PPPoEUser::with(['customer', 'serviceProfile', 'subscription', 'latestAccounting'])->findOrFail($id);
        
        // Redirect to a print view or open a print dialog
        session()->flash('info', 'Fitur print sedang dalam pengembangan! Data user: ' . $pppoeUser->username);
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Silakan pilih setidaknya satu PPPoE User untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete(PPPoEService $service)
    {
        Log::info(__METHOD__);
        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu PPPoE User untuk dihapus!');
                return;
            }

            foreach ($this->selectedIds as $id) {
                $pppoeUser = PPPoEUser::find($id);
                if ($pppoeUser) {
                    $service->terminatePPPoEUser($pppoeUser->id, Auth::id());
                    $pppoeUser->delete();
                }
            }

            session()->flash('success', count($this->selectedIds) . ' PPPoE User berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete PPPoE User failed', ['pppoe_user_ids' => $this->selectedIds, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus PPPoE User: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedIds;
            return Excel::download(new PPPoEUserExport($selectedIds), 'pppoe-users.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export PPPoE User: ' . $e->getMessage());
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
            Log::info('Importing PPPoE Users', ['user_id' => auth()->id()]);

            Excel::import(new PPPoEUserImport(auth()->user()), $this->importFile);

            Log::info('PPPoE Users imported successfully');

            if (session()->has('import_result')) {
                session()->flash('success', session('import_result'));
            } else {
                session()->flash('success', 'PPPoE User berhasil diimpor!');
            }
            $this->closeImportModal();

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            session()->flash('error', 'Gagal mengimpor: ' . implode('; ', $errors));
            $this->closeImportModal();
        } catch (Throwable $e) {
            Log::error('Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', $e->getMessage());
            $this->closeImportModal();
        }
    }

    public function render()
    {
        $query = PPPoEUser::with(['customer', 'serviceProfile', 'subscription', 'createdBy'])
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
            ->when(!empty($this->filters['status']), function($q) {
                if ($this->filters['status'] === 'online') {
                    $q->where('status', 'active')->whereHas('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } elseif ($this->filters['status'] === 'offline') {
                    $q->where('status', 'active')->whereDoesntHave('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } else {
                    $q->where('status', $this->filters['status']);
                }
            });

        $pppoeUsers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => PPPoEUser::count(),
            'active' => PPPoEUser::where('status', 'active')->count(),
            'inactive' => PPPoEUser::where('status', 'inactive')->count(),
            'suspended' => PPPoEUser::where('status', 'suspended')->count(),
            'online' => \App\Models\ISP\PPPoEUser::where('status', 'active')->whereHas('radiusAccountings', function($q) { $q->whereNull('acct_stop_time'); })->count(),
        ];

        return view('livewire.isp.pppoe-user.index', compact('pppoeUsers', 'stats'));
    }
}
