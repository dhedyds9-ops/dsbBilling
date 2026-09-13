<?php

namespace App\Livewire\ResellerPortal\Sales;


use App\Models\ISP\Voucher;
use App\Exports\VoucherExport;
use App\Imports\VoucherImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Src\Domain\Voucher\Actions\ManageVoucherAction;
use Src\Domain\Voucher\Queries\VoucherListQuery;
use Throwable;

class VoucherIndex extends \App\Livewire\ISP\BaseNetworkComponent
{
    use WithFileUploads;

    public bool $showTrashed = false;
    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $importFile;
    public bool $showImportModal = false;
    public bool $showFilterModal = false;
    public bool $showGenerateModal = false;
    public $nas_device_id;
    public $reseller_id;
    public $service_profile_id;
    public $login_method = 'voucher_code';
    public $quantity = 1;
    public $length = 6;
    public $prefix;
    public $code_combination = 'uppercase_alphanumeric';
    public $notes;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'sales.voucher';
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->filters = [
            'status' => '',
            'voucher_pool_id' => '',
            'created_date' => '',
        ];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Vouchers'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = $this->voucherQuery()->getListQuery(
                search: $this->search,
                filters: $this->filters,
                withTrashed: $this->showTrashed,
            )->where('vouchers.type', '!=', 'evoucher')->where('vouchers.reseller_id', auth()->id());

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

    public function clearSelection()
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Pilih minimal satu voucher untuk dicetak.');
            return;
        }

        $ids = implode(',', $this->selectedIds);
        $this->dispatch('open-print-window', ids: $ids);
    }

    public function printSingle($id)
    {
        $voucher = \App\Models\ISP\Voucher::find($id);
        if ($voucher && $voucher->voucher_pool_id) {
            // Print all vouchers in the same batch generated at the exact same time
            $ids = \App\Models\ISP\Voucher::where('voucher_pool_id', $voucher->voucher_pool_id)
                ->where('created_at', $voucher->created_at)
                ->pluck('id')
                ->toArray();
            $this->dispatch('open-print-window', ids: implode(',', $ids));
        } else {
            // Fallback to single print
            $this->dispatch('open-print-window', ids: $id);
        }
    }

    public function delete($id)
    {
        Log::info(__METHOD__);
        $action = $this->manageVoucher();
        $user = Auth::user();

        try {
            $voucher = Voucher::findOrFail($id);
            $action->delete($voucher, $user);
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
        $action = $this->manageVoucher();
        $user = Auth::user();

        try {
            $voucher = Voucher::withTrashed()->findOrFail($id);
            $action->restore($voucher, $user);
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
        $action = $this->manageVoucher();
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Voucher untuk dihapus!');
                return;
            }

            $action->bulkDelete($this->selectedIds, $user);
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
        $action = $this->manageVoucher();
        $user = Auth::user();

        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Voucher untuk direstore!');
                return;
            }

            $action->bulkRestore($this->selectedIds, $user);
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
        $this->filters = [
            'status' => '',
            'voucher_pool_id' => '',
            'created_date' => '',
        ];
        $this->search = '';
        $this->showTrashed = false;
        $this->resetActionState();
        $this->resetPage();
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function applyFilter()
    {
        $this->showFilterModal = false;
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

        public function generate(\Src\Domain\Voucher\Actions\GenerateVoucherAction $action)
    {
        $this->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'quantity' => 'required|integer|min:1|max:5000',
            'length' => 'required|integer|min:4|max:32',
            'nas_device_id' => 'nullable|exists:nas_devices,id',
            'reseller_id' => 'nullable|exists:users,id',
            'login_method' => 'required|in:voucher_code,username_password',
            'code_combination' => 'required|in:uppercase,lowercase,alphanumeric,numbers,uppercase_alphanumeric',
        ]);

        if (auth()->user()->hasRole(\App\Enums\UserRole::Reseller->value)) {
            $this->reseller_id = auth()->user()->getEffectiveResellerId();
        }

        try {
            $attrs = [
                'type' => 'hotspot',
                'nas_device_id' => $this->nas_device_id,
                'reseller_id' => $this->reseller_id,
                'service_profile_id' => $this->service_profile_id,
                'bind_on_login' => false,
                'fee_seller' => 0,
                'login_method' => $this->login_method,
                'code_combination' => $this->code_combination,
                'length' => (int)$this->length,
                'prefix' => $this->prefix ?: '',
                'notes' => $this->notes,
            ];

            $vouchers = $action->generateAdHocVouchers($attrs, (int) $this->quantity, (int) auth()->id());

            $this->showGenerateModal = false;
            
            $this->dispatch('voucher-generated-sweetalert', 
                count: count($vouchers), 
                ids: implode(',', array_column($vouchers, 'id'))
            );
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal generate voucher: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = $this->voucherQuery()->getListQuery(
            search: $this->search,
            filters: $this->filters,
            withTrashed: $this->showTrashed,
        )->where('vouchers.type', '!=', 'evoucher')->where('vouchers.reseller_id', auth()->id());

        $direction = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';

        switch ($this->sortField) {
            case 'id':
                $query->orderBy('vouchers.id', $direction);
                break;
            case 'username':
            case 'password':
            case 'code':
                $query->orderBy('vouchers.code', $direction);
                break;
            case 'service_profile_name':
                $query->orderBy('service_profiles.name', $direction);
                break;
            case 'selling_price':
                $query->orderByRaw("COALESCE(NULLIF(vouchers.fee_seller, 0), service_profiles.promo_price, service_profiles.base_price, 0) {$direction}");
                break;
            case 'server_name':
                $query->orderBy('nas_devices.name', $direction);
                break;
            case 'created_at':
                $query->orderBy('vouchers.created_at', $direction);
                break;
            case 'expires_at':
                $query->orderBy('vouchers.expires_at', $direction);
                break;
            case 'reseller_name':
                $query->orderBy('resellers.name', $direction);
                break;
            case 'status':
                $query->orderBy('vouchers.status', $direction);
                break;
            default:
                $query->orderBy('vouchers.id', 'desc');
                break;
        }

        $vouchers = $query->paginate((int) $this->perPage);

        // Stats calculation
        $statsQuery = clone $query;
        // avoid pagination for stats
        $statsQuery->limit(PHP_INT_MAX)->offset(0);
        
        $stats = [
            'total' => Voucher::where('reseller_id', auth()->id())->where('type', '!=', 'evoucher')->count(),
            'available' => Voucher::where('reseller_id', auth()->id())->where('type', '!=', 'evoucher')->where('status', 'available')->count(),
            'used' => Voucher::where('reseller_id', auth()->id())->where('type', '!=', 'evoucher')->where('status', 'used')->count(),
            'expired' => Voucher::where('reseller_id', auth()->id())->where('type', '!=', 'evoucher')->where('status', 'expired')->count(),
        ];

        $voucherPools = \App\Models\ISP\VoucherPool::orderBy('id', 'desc')->get(['id', 'name', 'created_at']);
        $nasDevices = \App\Models\ISP\NasDevice::active()->get();
        $userQueryService = app(\App\Services\Auth\UserQueryService::class);
        
        $serviceProfiles = \App\Models\ISP\ServiceProfile::active()
            ->where(function($q) {
                $q->where('service_type', 'voucher')
                  ->orWhere('service_type', 'hotspot')
                  ->orWhere('service_type', 'combined');
            })->get();

        return view('livewire.reseller-portal.sales.voucher', compact('vouchers', 'stats', 'voucherPools', 'nasDevices', 'serviceProfiles'));
    }

    private function voucherQuery(): VoucherListQuery
    {
        return app(VoucherListQuery::class);
    }

    private function manageVoucher(): ManageVoucherAction
    {
        return app(ManageVoucherAction::class);
    }
}
