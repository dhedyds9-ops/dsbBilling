<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Voucher;
use App\Exports\VoucherExport;
use App\Imports\VoucherImport;
use App\Services\ISP\VoucherService;
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
        $this->activePage = 'vouchers';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Vouchers'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = Voucher::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                            ->orWhereHas('owner', function($oq) {
                                $oq->where('name', 'like', '%' . $this->search . '%');
                            })
                            ->orWhereHas('serviceProfile', function($spq) {
                                $spq->where('name', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']));

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
        $service = app(VoucherService::class);
        $user = Auth::user();

        try {
            $voucher = Voucher::findOrFail($id);
            $service->delete($voucher, $user);
            session()->flash('success', 'Voucher berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete Voucher failed', ['voucher_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Voucher: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        Log::info(__METHOD__);
        $service = app(VoucherService::class);
        $user = Auth::user();

        try {
            $voucher = Voucher::withTrashed()->findOrFail($id);
            $service->restore($voucher, $user);
            session()->flash('success', 'Voucher berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore Voucher failed', ['voucher_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Voucher: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Silakan pilih setidaknya satu Voucher untuk dihapus!');
            return;
        }
        $this->showDeleteModal = true;
    }

    public function bulkDelete()
    {
        Log::info(__METHOD__);
        $service = app(VoucherService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Voucher untuk dihapus!');
                return;
            }

            $service->bulkDelete($this->selectedIds, $user);
            session()->flash('success', count($this->selectedIds) . ' Voucher berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk delete Voucher failed', ['voucher_ids' => $this->selectedIds, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Voucher: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        Log::info(__METHOD__);
        $service = app(VoucherService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Voucher untuk direstore!');
                return;
            }

            $service->bulkRestore($this->selectedIds, $user);
            session()->flash('success', count($this->selectedIds) . ' Voucher berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Bulk restore Voucher failed', ['voucher_ids' => $this->selectedIds, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Voucher: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info(__METHOD__);
        try {
            $selectedIds = $this->selectedIds;
            return Excel::download(new VoucherExport($selectedIds), 'vouchers.xlsx');
        } catch (Throwable $e) {
            Log::error('Export Failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export voucher: ' . $e->getMessage());
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
            Log::info('Importing vouchers', ['user_id' => auth()->id()]);

            Excel::import(new VoucherImport(auth()->user()), $this->importFile);

            Log::info('Vouchers imported successfully');

            session()->flash('success', 'Voucher berhasil diimpor!');
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
        $query = Voucher::with(['serviceProfile', 'customer', 'nasDevice', 'owner'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('owner', function($oq) {
                            $oq->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('serviceProfile', function($spq) {
                            $spq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filters['status'], function($q) {
                $q->where('status', $this->filters['status']);
            });

        $vouchers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => Voucher::count(),
            'available' => Voucher::where('status', 'available')->count(),
            'used' => Voucher::where('status', 'used')->count(),
            'expired' => Voucher::where('status', 'expired')->count(),
        ];

        return view('livewire.isp.voucher.index', compact('vouchers', 'stats'));
    }
}
