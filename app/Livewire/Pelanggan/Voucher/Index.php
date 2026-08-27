<?php

namespace App\Livewire\Pelanggan\Voucher;

use App\Livewire\BaseEnterpriseList;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use App\Services\Pelanggan\VoucherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'pelanggan';
    public string $activePage = 'voucher';

    public string $activeTab = 'all';
    public array $tabs = [
        'all' => ['label' => 'Semua', 'count' => 0],
        'active' => ['label' => 'Aktif', 'count' => 0],
        'used' => ['label' => 'Terpakai', 'count' => 0],
        'expired' => ['label' => 'Expired', 'count' => 0],
    ];

    public array $summary = [];
    public array $filterOptions = [];
    public array $exportRows = [];

    public bool $showGenerateModal = false;
    public array $generateParams = [
        'package_id' => '',
        'router_id' => '',
        'reseller_id' => '',
        'count' => 10,
        'type' => 'reguler',
        'prefix' => '',
        'length' => 8,
        'validity_days' => '',
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
        $this->loadSummary();
        $this->loadFilterOptions();
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) {
            abort(403);
        }
        if (!\Gate::allows('pelanggan.voucher.view') && !\Gate::allows('*') && !Auth::user()?->hasRole('admin')) {
            // fallback auth check, proceed
        }
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function loadSummary(): void
    {
        $counts = app(VoucherService::class)->summaryCounts();
        $this->summary = $counts;
        $this->tabs = [
            'all' => ['label' => 'Semua', 'count' => $counts['all']],
            'active' => ['label' => 'Aktif', 'count' => $counts['active']],
            'used' => ['label' => 'Terpakai', 'count' => $counts['used']],
            'expired' => ['label' => 'Expired', 'count' => $counts['expired']],
        ];
    }

    public function loadFilterOptions(): void
    {
        $this->filterOptions = [
            'routers' => Router::pluck('name', 'id')->all(),
            'packages' => ServiceProfile::active()->pluck('name', 'id')->all(),
            'sales' => User::pluck('name', 'id')->all(),
            'resellers' => User::pluck('name', 'id')->all(),
            'wilayahs' => \App\Models\ISP\Pop::pluck('name', 'id')->all(),
        ];
    }

    public function setActiveTab(string $k): void
    {
        $this->activeTab = $k;
    }

    public function getRowsQuery()
    {
        $svc = app(VoucherService::class);
        return $svc->list(
            tab: $this->activeTab,
            search: $this->search,
            filters: $this->filters,
            sort: $this->sortField,
            dir: $this->sortDirection,
            page: 1,
            perpage: min(500, $this->perPage * 10),
        )->getQuery();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $svc = app(VoucherService::class);
            $rows = $svc->list(
                tab: $this->activeTab,
                search: $this->search,
                filters: $this->filters,
                sort: $this->sortField,
                dir: $this->sortDirection,
                page: \Illuminate\Pagination\Paginator::resolveCurrentPage('page'),
                perpage: $this->perPage,
            );
            $this->exportRows = $rows->items();
            return $rows;
        }, 'Gagal memuat data voucher');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return $this->withLoading(function () use ($action, $ids) {
            $svc = app(VoucherService::class);
            return match ($action) {
                'sync' => $svc->bulkSyncRouter($ids),
                'disable' => $svc->bulkDisable($ids),
                'delete' => $svc->bulkDelete($ids),
                'export' => (function () {
                    $svc = app(VoucherService::class);
                    return collect($this->selected)->count();
                })(),
                default => 0,
            };
        }, 'Gagal memproses bulk action') ?? 0;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $svc = app(VoucherService::class);
            $ids = count($this->selected) > 0 ? $this->selected : null;

            if ($ids) {
                $rows = \App\Models\ISP\Voucher::query()
                    ->leftJoin('hotspot_users', 'vouchers.hotspot_user_id', '=', 'hotspot_users.id')
                    ->leftJoin('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
                    ->leftJoin('members', 'customer_services.customer_id', '=', 'members.id')
                    ->leftJoin('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
                    ->leftJoin('nas_devices', 'vouchers.nas_device_id', '=', 'nas_devices.id')
                    ->whereIn('vouchers.id', $ids)
                    ->select([
                        'vouchers.code',
                        'members.name as customer_name',
                        'service_profiles.name as package_name',
                        'nas_devices.name as router_name',
                        'service_profiles.base_price as price',
                        'service_profiles.validity_days as duration',
                        'vouchers.status',
                        'vouchers.created_at',
                        'vouchers.activated_at as redeemed_at',
                    ])->get();
            } else {
                $rows = collect($this->exportRows);
            }

            return $svc->exportCsv($rows);
        } catch (\Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function confirmRowAction(string $action, string|int $id): void
    {
        $labels = [
            'detail' => 'melihat detail',
            'sync' => 'menyinkronkan',
            'disable' => 'menonaktifkan',
            'delete' => 'menghapus',
        ];
        $verb = $labels[$action] ?? 'memproses';
        $this->confirmTitle = ucfirst($verb) . ' voucher';
        $this->confirmMessage = "Anda akan {$verb} voucher terpilih. Lanjutkan?";
        $this->confirmAction = 'row-action';
        $this->confirmParams = ['action' => $action, 'id' => $id];
        $this->confirmBtnText = $action === 'delete' ? 'Hapus' : 'Ya';
        $this->confirmBtnClass = $action === 'delete'
            ? 'bg-red-600 hover:bg-red-700 text-white'
            : 'bg-blue-600 hover:bg-blue-700 text-white';

        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'row-action') {
            $act = $this->confirmParams['action'] ?? '';
            $id = $this->confirmParams['id'] ?? null;
            if ($id !== null) {
                $this->withLoading(function () use ($act, $id) {
                    $svc = app(VoucherService::class);
                    match ($act) {
                        'sync' => $svc->bulkSyncRouter([$id]),
                        'disable' => $svc->bulkDisable([$id]),
                        'delete' => $svc->bulkDelete([$id]),
                        default => null,
                    };
                    session()->flash('success', ucfirst($act) . ' voucher berhasil.');
                    return null;
                }, 'Aksi voucher gagal');
            }
        } else {
            parent::handleConfirm();
            return;
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
        $this->confirmAction = '';
        $this->confirmParams = [];
        $this->selected = [];
        $this->resetPage();
    }

    public function openGenerate(): void
    {
        $this->showGenerateModal = true;
        $this->generateParams = [
            'package_id' => '',
            'router_id' => '',
            'reseller_id' => '',
            'count' => 10,
            'type' => 'reguler',
            'prefix' => '',
            'length' => 8,
            'validity_days' => '',
            'notes' => '',
        ];
    }

    public function closeGenerate(): void
    {
        $this->showGenerateModal = false;
    }

    public function submitGenerate(): void
    {
        $valid = $this->validate([
            'generateParams.package_id' => ['required', Rule::exists('service_profiles', 'id')],
            'generateParams.count' => ['required', 'integer', 'min:1', 'max:10000'],
            'generateParams.type' => ['required', Rule::in(['reguler', 'evoucher'])],
            'generateParams.length' => ['nullable', 'integer', 'min:4', 'max:32'],
            'generateParams.validity_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->withLoading(function () {
            $svc = app(VoucherService::class);
            $params = array_filter($this->generateParams, fn($v) => $v !== '' && $v !== null);
            if (!empty($params['validity_days'])) {
                $params['validity_days'] = (int) $params['validity_days'];
            }
            $created = $svc->generateVouchers($params);
            session()->flash('success', count($created) . ' voucher berhasil digenerate.');
            $this->showGenerateModal = false;
            $this->loadSummary();
            $this->resetPage();
            return $created;
        }, 'Generate voucher gagal');
    }

    public function printSelected(): void
    {
        session()->flash('info', 'Print voucher dalam pengembangan.');
    }

    public function syncRouterAll(): void
    {
        $this->withLoading(function () {
            $svc = app(VoucherService::class);
            $ids = count($this->selected) > 0 ? $this->selected
                : \App\Models\ISP\Voucher::query()->limit(200)->pluck('id')->map(fn($v) => (string)$v)->all();
            $n = $svc->bulkSyncRouter($ids);
            session()->flash('success', "Berhasil sinkron {$n} voucher ke router.");
            return null;
        }, 'Sync router gagal');
    }

    public function render()
    {
        $rows = $this->getRows();

        return view('livewire.pelanggan.voucher.index', [
            'rows' => $rows,
        ]);
    }
}
