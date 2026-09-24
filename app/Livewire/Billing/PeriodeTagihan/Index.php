<?php

namespace App\Livewire\Billing\PeriodeTagihan;

use App\Livewire\BaseEnterpriseList;
use App\Models\Billing\Invoice;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use App\Services\Billing\PeriodeTagihanService;
use Illuminate\Support\Facades\Auth;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'billing';
    public string $activePage = 'periode-tagihan';

    public string $activeTab = 'all';
    public array $tabs = [
        'all' => 'Semua',
        'unpaid' => 'Belum Bayar',
        'paid' => 'Lunas',
        'overdue' => 'Overdue',
    ];

    public array $summary = [];
    public array $filterOptions = [];
    public array $exportRows = [];

    public int $generateTahun;
    public int $generateBulan;
    public bool $showGenerateModal = false;
    public bool $showRegenerateModal = false;

    public function mount(): void
    {
        $this->generateTahun = (int) now()->year;
        $this->generateBulan = (int) now()->month;

        $this->filters = [
            'tahun' => (string) now()->year,
            'bulan' => (string) now()->month,
            'status' => '',
            'router_id' => '',
            'sales_id' => '',
            'reseller_id' => '',
            'package_id' => '',
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
        $this->summary = app(PeriodeTagihanService::class)->summary($this->filters);
    }

    public function loadFilterOptions(): void
    {
        $this->filterOptions = [
            'routers' => Router::pluck('name', 'id')->all(),
            'packages' => ServiceProfile::active()->pluck('name', 'id')->all(),
            'sales' => User::pluck('name', 'id')->all(),
            'resellers' => User::pluck('name', 'id')->all(),
            'tahun' => collect(range(now()->year - 3, now()->year + 1))->mapWithKeys(fn($v) => [$v => $v])->all(),
            'bulan' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
        ];
    }

    public function setActiveTab(string $k): void
    {
        $this->activeTab = $k;
        if ($k !== 'all') {
            $this->filters['status'] = $k === 'unpaid' ? 'unpaid' : $k;
        } else {
            unset($this->filters['status']);
        }
    }

    public function updatedActiveTab(string $value = ''): void
    {
        parent::updatedActiveTab($value);
        $this->loadSummary();
    }

    public function updatedFilters(mixed $value = null, ?string $key = null): void
    {
        parent::updatedFilters($value, $key);
        $this->loadSummary();
    }

    public function getRowsQuery()
    {
        $svc = app(PeriodeTagihanService::class);
        return $svc->aggregatePeriode(
            filters: $this->filters,
            search: $this->search,
            sort: $this->sortField,
            dir: $this->sortDirection,
        );
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            $svc = app(PeriodeTagihanService::class);
            $rows = $svc->aggregatePeriode(
                filters: $this->filters,
                search: $this->search,
                sort: $this->sortField,
                dir: $this->sortDirection,
            );
            $this->exportRows = $rows->all();
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
            $perpage = $this->perPage;
            $offset = ($page - 1) * $perpage;
            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: $rows->slice($offset, $perpage)->values(),
                total: $rows->count(),
                perPage: $perpage,
                currentPage: $page,
                options: ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }, 'Gagal memuat data periode tagihan');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return $this->withLoading(function () use ($action, $ids) {
            $svc = app(PeriodeTagihanService::class);
            $userId = Auth::id() ?? 0;

            return match ($action) {
                'generate' => (function () use ($svc, $ids, $userId) {
                    $total = 0;
                    foreach ($ids as $periodeKey) {
                        [$tahun, $bulan] = explode('-', (string)$periodeKey) + [0 => null, 1 => null];
                        if ($tahun && $bulan) {
                            $res = $svc->generateForPeriod((int)$tahun, (int)$bulan, $userId);
                            $total += $res['count'] ?? 0;
                        }
                    }
                    return $total;
                })(),
                'regenerate' => (function () use ($svc, $ids, $userId) {
                    $total = 0;
                    foreach ($ids as $periodeKey) {
                        [$tahun, $bulan] = explode('-', (string)$periodeKey) + [0 => null, 1 => null];
                        if ($tahun && $bulan) {
                            $res = $svc->generateForPeriod((int)$tahun, (int)$bulan, $userId);
                            $total += $res['count'] ?? 0;
                        }
                    }
                    return $total;
                })(),
                'send-wa' => (function () use ($svc, $ids) {
                    $invIds = [];
                    foreach ($ids as $periodeKey) {
                        [$tahun, $bulan] = explode('-', (string)$periodeKey) + [0 => null, 1 => null];
                        if ($tahun && $bulan) {
                            $periodeIds = Invoice::query()
                                ->whereYear('issue_date', (int)$tahun)
                                ->whereMonth('issue_date', (int)$bulan)
                                ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                                ->pluck('id')
                                ->all();
                            $invIds = array_merge($invIds, $periodeIds);
                        }
                    }
                    return $svc->sendWaBulk(array_unique($invIds));
                })(),
                'export' => count($ids),
                default => 0,
            };
        }, 'Gagal memproses bulk action') ?? 0;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $svc = app(PeriodeTagihanService::class);
            $rows = collect($this->exportRows);
            return $svc->exportCsv($rows);
        } catch (\Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function confirmRowAction(string $action, int $tahun, int $bulan): void
    {
        $labels = [
            'generate' => 'generate tagihan',
            'regenerate' => 'regenerate tagihan',
            'send-wa' => 'kirim WA reminder',
            'export' => 'export periode',
        ];
        $verb = $labels[$action] ?? 'memproses';
        $this->confirmTitle = ucfirst($verb);
        $this->confirmMessage = "Anda akan {$verb} untuk periode {$bulan}/{$tahun}. Lanjutkan?";
        $this->confirmAction = 'row-action';
        $this->confirmParams = ['action' => $action, 'tahun' => $tahun, 'bulan' => $bulan];
        $this->confirmBtnText = 'Ya';
        $this->confirmBtnClass = 'bg-blue-600 hover:bg-blue-700 text-white';

        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'row-action') {
            $act = $this->confirmParams['action'] ?? '';
            $tahun = $this->confirmParams['tahun'] ?? null;
            $bulan = $this->confirmParams['bulan'] ?? null;

            $this->withLoading(function () use ($act, $tahun, $bulan) {
                $svc = app(PeriodeTagihanService::class);
                $userId = Auth::id() ?? 0;
                if ($act === 'generate' || $act === 'regenerate') {
                    $res = $svc->generateForPeriod((int)$tahun, (int)$bulan, $userId);
                    session()->flash('success', "Generate periode {$bulan}/{$tahun}: {$res['count']} invoice dibuat.");
                } elseif ($act === 'send-wa') {
                    $invIds = Invoice::query()
                        ->whereYear('issue_date', (int)$tahun)
                        ->whereMonth('issue_date', (int)$bulan)
                        ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                        ->pluck('id')
                        ->all();
                    $n = $svc->sendWaBulk($invIds);
                    session()->flash('success', "WA reminder untuk {$n} invoice siap dikirim.");
                } elseif ($act === 'export') {
                    session()->flash('info', 'Export periode dimulai.');
                }
                $this->loadSummary();
                return null;
            }, 'Aksi periode gagal');
        } else {
            parent::handleConfirm();
            return;
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
        $this->confirmAction = '';
        $this->confirmParams = [];
        $this->resetPage();
    }

    public function openGenerate(): void
    {
        $this->generateTahun = (int) ($this->filters['tahun'] ?? now()->year);
        $this->generateBulan = (int) ($this->filters['bulan'] ?? now()->month);
        $this->showGenerateModal = true;
    }

    public function closeGenerate(): void
    {
        $this->showGenerateModal = false;
    }

    public function submitGenerate(): void
    {
        $this->withLoading(function () {
            $svc = app(PeriodeTagihanService::class);
            $userId = Auth::id() ?? 0;
            $res = $svc->generateForPeriod($this->generateTahun, $this->generateBulan, $userId);
            session()->flash('success', "Generate periode {$this->generateBulan}/{$this->generateTahun}: {$res['count']} invoice baru.");
            $this->showGenerateModal = false;
            $this->loadSummary();
            $this->resetPage();
            return $res;
        }, 'Generate periode gagal');
    }

    public function openRegenerate(): void
    {
        $this->generateTahun = (int) ($this->filters['tahun'] ?? now()->year);
        $this->generateBulan = (int) ($this->filters['bulan'] ?? now()->month);
        $this->showRegenerateModal = true;
    }

    public function closeRegenerate(): void
    {
        $this->showRegenerateModal = false;
    }

    public function submitRegenerate(): void
    {
        $this->submitGenerate();
        $this->showRegenerateModal = false;
    }

    public function kirimWaSemua(): void
    {
        $this->withLoading(function () {
            $svc = app(PeriodeTagihanService::class);
            $tahun = (int) ($this->filters['tahun'] ?? now()->year);
            $bulan = (int) ($this->filters['bulan'] ?? now()->month);
            $invIds = Invoice::query()
                ->when($tahun, fn($q) => $q->whereYear('issue_date', $tahun))
                ->when($bulan, fn($q) => $q->whereMonth('issue_date', $bulan))
                ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                ->pluck('id')
                ->all();
            $n = $svc->sendWaBulk($invIds);
            session()->flash('success', "WA reminder disiapkan untuk {$n} invoice unpaid.");
            return null;
        }, 'Kirim WA gagal');
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.billing.periode-tagihan.index', [
            'rows' => $rows,
        ]);
    }
}
