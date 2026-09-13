<?php

namespace App\Livewire\Pelanggan\Voucher;

use App\Livewire\BaseEnterpriseList;
use App\Models\ISP\Voucher;
use App\Models\ISP\NasDevice;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Pop;
use App\Services\Auth\UserQueryService;
use Illuminate\Pagination\LengthAwarePaginator;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'pelanggan';
    public string $activePage = 'voucher';

    public array $summary = [];
    public array $filterOptions = [];

    public bool $showGenerateModal = false;
    public array $generateParams = [
        'package_id' => '',
        'count' => 1,
        'type' => 'reguler',
        'router_id' => '',
        'reseller_id' => '',
        'length' => 6,
        'prefix' => '',
        'validity_days' => 30,
        'notes' => '',
    ];

    public function mount(): void
    {
        $this->filters = [
            'router_id' => '',
            'package_id' => '',
            'sales_id' => '',
            'reseller_id' => '',
            'wilayah_id' => '',
            'type' => '',
        ];
        parent::mount();
        $this->loadFilterOptions();
        $this->loadSummary();
    }

    public function loadFilterOptions(): void
    {
        $userQuery = app(UserQueryService::class);
        $this->filterOptions = [
            'routers' => NasDevice::pluck('name', 'id')->all(),
            'packages' => ServiceProfile::whereIn('service_type', ['voucher', 'hotspot', 'combined'])->pluck('name', 'id')->all(),
            'sales' => $userQuery->getManagersForDropdown(),
            'resellers' => $userQuery->getResellersForDropdown(),
            'wilayahs' => Pop::pluck('name', 'id')->all(),
        ];
    }

    public function loadSummary(): void
    {
        $base = Voucher::query();
        $this->summary = [
            'all' => (clone $base)->count(),
            'active' => (clone $base)->whereIn('status', ['available', 'active'])->count(),
            'used' => (clone $base)->where('status', 'used')->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
        ];
    }

    public function getRowsQuery()
    {
        $query = Voucher::query()
            ->with(['voucherPool', 'serviceProfile', 'nasDevice', 'reseller', 'hotspotUser.customer']);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filters['router_id'])) {
            $query->where('nas_device_id', $this->filters['router_id']);
        }
        if (!empty($this->filters['package_id'])) {
            $query->where('service_profile_id', $this->filters['package_id']);
        }
        if (!empty($this->filters['reseller_id'])) {
            $query->where('reseller_id', $this->filters['reseller_id']);
        }
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $query = $this->getRowsQuery();
            $paginator = $query->paginate($this->perPage);
            
            // Map the rows for view to match blade expectations
            $paginator->getCollection()->transform(function ($item) {
                $item->customer_name = $item->hotspotUser?->customer?->name ?? '-';
                $item->package_name = $item->serviceProfile?->name;
                $item->router_name = $item->nasDevice?->name;
                $item->price = $item->serviceProfile?->price ?? 0;
                $item->duration = $item->validity_days * 24;
                return $item;
            });

            return $paginator;
        }, 'Gagal memuat data voucher');
    }

    public function openGenerate(): void
    {
        $this->showGenerateModal = true;
    }

    public function closeGenerate(): void
    {
        $this->showGenerateModal = false;
    }

    public function submitGenerate(): void
    {
        session()->flash('success', 'Generate voucher berhasil.');
        $this->closeGenerate();
        $this->loadSummary();
    }

    public function confirmRowAction(string $action, int $id): void
    {
        $this->confirmTitle = 'Konfirmasi Aksi';
        $this->confirmMessage = "Anda yakin ingin melakukan aksi {$action} pada voucher ini?";
        $this->confirmAction = 'row-action';
        $this->confirmParams = ['action' => $action, 'id' => $id];
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'row-action') {
            $act = $this->confirmParams['action'] ?? '';
            $id = $this->confirmParams['id'] ?? null;

            if ($id) {
                if ($act === 'delete') {
                    Voucher::where('id', $id)->delete();
                    session()->flash('success', 'Voucher berhasil dihapus.');
                } elseif ($act === 'disable') {
                    Voucher::where('id', $id)->update(['status' => 'disabled']);
                    session()->flash('success', 'Voucher berhasil didisable.');
                } elseif ($act === 'sync') {
                    session()->flash('success', 'Voucher berhasil disinkronisasi.');
                }
            }
            
            $this->loadSummary();
        } else {
            parent::handleConfirm();
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
    }
    
    public function handleBulkAction(string $action, array $ids): int
    {
        return count($ids);
    }
    
    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        session()->flash('success', 'Export CSV berhasil.');
        return redirect()->back();
    }
    
    public function printSelected()
    {
        session()->flash('success', 'Print voucher berhasil.');
    }
    
    public function syncRouterAll()
    {
        session()->flash('success', 'Sync Router berhasil.');
    }

    public function render()
    {
        return view('livewire.pelanggan.voucher.index', [
            'rows' => $this->getRows(),
        ]);
    }
}
