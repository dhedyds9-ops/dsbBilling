<?php

namespace App\Livewire\Pelanggan\Isolir;

use App\Livewire\BaseEnterpriseList;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use App\Services\Pelanggan\IsolirService;
use Illuminate\Support\Facades\Auth;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'pelanggan';
    public string $activePage = 'isolir';

    public array $summary = [];
    public array $filterOptions = [];
    public array $exportRows = [];

    public bool $showPerpanjangModal = false;
    public bool $showGantiPaketModal = false;
    public array $selectedUser = [];
    public array $formParams = [
        'customer_service_id' => '',
        'package_id' => '',
        'notes' => '',
        'grace_days' => 3,
    ];

    public function mount(): void
    {
        $this->filters = [
            'router_id' => '',
            'package_id' => '',
            'sales_id' => '',
            'reseller_id' => '',
            'wilayah_id' => '',
            'start_date' => '',
            'end_date' => '',
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
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function loadSummary(): void
    {
        $this->summary = app(IsolirService::class)->summary();
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

    public function getRowsQuery()
    {
        $svc = app(IsolirService::class);
        $rows = $svc->list(
            search: $this->search,
            filters: $this->filters,
            sort: $this->sortField,
            dir: $this->sortDirection,
            page: 1,
            perpage: min(500, $this->perPage * 10),
        );
        return $rows->getQuery();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $svc = app(IsolirService::class);
            $rows = $svc->list(
                search: $this->search,
                filters: $this->filters,
                sort: $this->sortField,
                dir: $this->sortDirection,
                page: \Illuminate\Pagination\Paginator::resolveCurrentPage('page'),
                perpage: $this->perPage,
            );
            $this->exportRows = $rows->items();
            return $rows;
        }, 'Gagal memuat data isolir');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return $this->withLoading(function () use ($action, $ids) {
            $svc = app(IsolirService::class);
            return match ($action) {
                'activate' => $svc->bulkActivate($ids),
                'send-wa' => $svc->sendWaBulk($ids),
                'export' => count($ids),
                default => 0,
            };
        }, 'Gagal memproses bulk action') ?? 0;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $svc = app(IsolirService::class);
            $rows = count($this->selected) > 0
                ? collect($this->selected)->map(function ($id) use ($svc) {
                    // light: for export we use rows already loaded
                    return (object) ['source_type' => '-', 'username' => $id, 'customer_name' => '', 'package_name' => '',
                        'status' => '', 'alasan' => '', 'since_isolir' => '', 'last_due_date' => ''];
                })
                : collect($this->exportRows);
            if (count($this->selected) === 0) {
                $rows = collect($this->exportRows);
            }
            return $svc->exportCsv($rows);
        } catch (\Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function confirmRowAction(string $action, string|int $id, ?string $phone = null, ?string $name = null): void
    {
        $labels = [
            'activate' => 'mengaktifkan kembali',
            'perpanjang' => 'memperpanjang masa aktif',
            'ganti-paket' => 'mengganti paket',
            'wa' => 'mengirim WhatsApp',
        ];
        $verb = $labels[$action] ?? 'memproses';

        if ($action === 'wa') {
            $msg = "Halo {$name}, layanan internet Anda sedang dalam status isolir karena ada tagihan yang belum dibayar. Silakan lakukan pembayaran agar layanan dapat diaktifkan kembali. Terima kasih.";
            $waLink = 'https://wa.me/' . preg_replace('/\D+/', '', (string)$phone) . '?text=' . urlencode($msg);
            $this->dispatch('open-tab', url: $waLink);
            if (function_exists('redirect')) {
                // noop
            }
            return;
        }

        $this->confirmTitle = ucfirst($verb) . ' pelanggan';
        $this->confirmMessage = "Anda akan {$verb} pelanggan terpilih. Lanjutkan?";
        $this->confirmAction = 'row-action';
        $this->confirmParams = ['action' => $action, 'id' => $id, 'name' => $name, 'phone' => $phone];
        $this->confirmBtnText = 'Ya';
        $this->confirmBtnClass = 'bg-blue-600 hover:bg-blue-700 text-white';

        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'row-action') {
            $act = $this->confirmParams['action'] ?? '';
            $id = $this->confirmParams['id'] ?? null;

            if ($act === 'perpanjang') {
                $this->selectedUser = ['id' => $id, 'name' => $this->confirmParams['name'] ?? ''];
                $this->formParams = [
                    'customer_service_id' => '',
                    'package_id' => '',
                    'notes' => '',
                    'grace_days' => 3,
                ];
                $this->dispatch('close-modal', name: $this->confirmModal);
                $this->showPerpanjangModal = true;
                return;
            }
            if ($act === 'ganti-paket') {
                $this->selectedUser = ['id' => $id, 'name' => $this->confirmParams['name'] ?? ''];
                $this->formParams = [
                    'customer_service_id' => '',
                    'package_id' => '',
                    'notes' => '',
                ];
                $this->dispatch('close-modal', name: $this->confirmModal);
                $this->showGantiPaketModal = true;
                return;
            }

            if ($id !== null) {
                $this->withLoading(function () use ($act, $id) {
                    $svc = app(IsolirService::class);
                    match ($act) {
                        'activate' => $svc->reactivateUser($id),
                        default => null,
                    };
                    session()->flash('success', ucfirst($act) . ' berhasil.');
                    $this->loadSummary();
                    return null;
                }, 'Aksi gagal');
            }
        } else {
            parent::handleConfirm();
            return;
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
        $this->confirmAction = '';
        $this->confirmParams = [];
        $this->resetPage();
    }

    public function closeModals(): void
    {
        $this->showPerpanjangModal = false;
        $this->showGantiPaketModal = false;
        $this->selectedUser = [];
    }

    public function submitPerpanjang(): void
    {
        $this->validate([
            'formParams.grace_days' => 'required|integer|min:1|max:7',
        ]);

        try {
            $svc = app(IsolirService::class);
            $parsed = $svc->parseDisplayId($this->selectedUser['id'] ?? '');
            if (!$parsed || !$parsed[0]) {
                throw new \Exception('Data pelanggan tidak valid.');
            }

            [$type, $realId] = $parsed;
            $customer = null;

            if ($type === 'pppoe') {
                $user = \App\Models\ISP\PPPoEUser::with('customer')->find($realId);
                $customer = $user?->customer;
            } else {
                $user = \App\Models\ISP\HotspotUser::with('customer')->find($realId);
                $customer = $user?->customer;
            }

            if (!$customer) {
                throw new \Exception('Pelanggan tidak ditemukan.');
            }

            $invoice = \App\Models\Billing\Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'overdue', 'partial', 'pending'])
                ->latest('due_date')
                ->first();

            if (!$invoice) {
                throw new \Exception('Tidak ada tagihan tertunggak untuk pelanggan ini.');
            }

            if ($invoice->hasActiveGracePeriod()) {
                throw new \Exception('Pelanggan ini sedang dalam masa Janji Bayar hingga ' . $invoice->grace_period_until->format('d/m/Y'));
            }

            // Batasi janji bayar maksimal 1 kali jika sudah pernah ada tapi lewat (opsional)
            // if ($invoice->grace_period_until !== null) { ... }

            $days = (int)$this->formParams['grace_days'];
            $invoice->grace_period_until = now()->addDays($days);
            $invoice->save();

            // Reactivate the user since they are given grace period
            $svc->reactivateUser($this->selectedUser['id']);

            session()->flash('success', "Janji Bayar berhasil diberikan selama {$days} hari. Pelanggan telah diaktifkan kembali.");
            $this->loadSummary();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }

        $this->closeModals();
    }

    public function submitGantiPaket(): void
    {
        session()->flash('success', 'Ganti paket berhasil dicatat (demo).');
        $this->closeModals();
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.pelanggan.isolir.index', [
            'rows' => $rows,
        ]);
    }
}
