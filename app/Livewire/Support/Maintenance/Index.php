<?php

namespace App\Livewire\Support\Maintenance;

use App\Livewire\BaseEnterpriseList;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\Pop;
use App\Models\ISP\Router as RouterModel;
use App\Models\ISP\Tower;
use App\Models\Notification\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'support';
    public string $activePage = 'maintenance';
    public string $sortField = 'scheduled_date';

    public array $tabs = [
        'calendar' => 'Kalender',
        'history' => 'Daftar & Riwayat',
        'technician' => 'Beban Teknisi',
        'material' => 'Material & Stok',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $calendar = null;
    public ?array $calendarEvents = null;
    public ?array $technicianStats = null;
    public ?array $technicianLoad = null;
    public ?array $materialStock = null;
    public ?array $materialUsage = null;
    public ?array $stock = null;
    public ?array $usage = null;
    public array $inventoryAssetSummary = ['total' => 0, 'available' => 0];
    public array $warehouseOptions = [];
    public array $maintenanceScheduleOptions = [];

    // Schedule Modals
    public bool $showCreateModal = false;
    public array $newMaint = [];
    public string $newMaintDeviceSelect = '';

    public bool $showEditModal = false;
    public array $editMaint = [];
    public string $editMaintDeviceSelect = '';

    public bool $showCompleteModal = false;
    public ?int $completeMaintId = null;
    public string $completeMaintTitle = '';
    public string $completeResolutionNotes = '';

    public bool $showDetailModal = false;
    public ?array $detailRow = null;
    public array $detailUsages = [];

    // Material Stock & Usage Modals
    public bool $showMaterialModal = false;
    public ?int $editingMaterialId = null;
    public array $materialForm = [];

    public bool $showUsageModal = false;
    public array $usageForm = [];

    public string $calendarMonth = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (
            $user
            && !$user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) === 'TECHNICIAN'
        ) {
            $this->redirect(route('technician.my-jobs.maintenance'));
            return;
        }

        $requestedTab = (string) request()->query('activeTab', $this->activeTab);

        parent::mount();

        $this->activeTab = ($requestedTab !== '' && isset($this->tabs[$requestedTab]))
            ? $requestedTab
            : 'history';

        $this->calendarMonth = now()->format('Y-m');
        $this->filters = array_merge([
            'status' => '',
            'type' => '',
            'technician_id' => '',
            'device_type' => '',
            'scheduled_from' => '',
            'scheduled_to' => '',
        ], $this->filters ?: []);

        $this->resetNewMaintForm();
        $this->resetMaterialForm();
        $this->resetUsageForm();

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Support', 'url' => '#'],
            ['label' => 'Maintenance Jaringan', 'url' => route('support.maintenance')],
        ];

        $this->loadAll();
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

    protected function tableName(): string
    {
        return 'support_maintenances';
    }

    protected function resetNewMaintForm(): void
    {
        $this->newMaintDeviceSelect = '';
        $this->newMaint = [
            'type' => 'preventive',
            'title' => '',
            'device_type' => 'router',
            'device_name' => '',
            'location_name' => '',
            'scheduled_date' => now()->addDays(1)->toDateString(),
            'scheduled_time' => '09:00',
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
    }

    protected function resetMaterialForm(): void
    {
        $this->editingMaterialId = null;
        $this->materialForm = [
            'sku' => '',
            'name' => '',
            'category' => 'Fiber Optic',
            'stock' => 0,
            'min_stock' => 5,
            'unit' => 'pcs',
            'location' => 'Gudang Utama',
            'notes' => '',
        ];
    }

    protected function resetUsageForm(): void
    {
        $this->usageForm = [
            'maintenance_id' => '',
            'material_id' => '',
            'material_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'technician_id' => '',
            'technician_name' => '',
            'used_date' => now()->toDateString(),
            'notes' => '',
        ];
    }

    /**
     * Ambil daftar staf teknis internal aktif (bukan akun Pelanggan / Reseller).
     */
    protected function fetchTechnicalStaff(): array
    {
        try {
            $users = User::query()
                ->with('roles')
                ->where('is_active', true)
                ->whereNull('reseller_id')
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['administrator', 'superadmin', 'manager']);
                })
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['customer', 'reseller']);
                })
                ->orderByRaw("CASE WHEN UPPER(COALESCE(job_function, '')) = 'TECHNICIAN' THEN 0 WHEN UPPER(COALESCE(job_function, '')) = 'NOC' THEN 1 ELSE 2 END")
                ->orderBy('name')
                ->get(['id', 'name', 'job_function', 'job_title']);

            $result = [];
            foreach ($users as $u) {
                $jf = strtoupper((string) ($u->job_function ?? ''));
                $roleTag = match ($jf) {
                    'TECHNICIAN' => 'Teknisi',
                    'NOC' => 'NOC',
                    default => $u->job_title ?: ($u->hasRole('administrator') ? 'Admin' : 'Staf'),
                };
                $result[$u->id] = $u->name . ' (' . $roleTag . ')';
            }

            return $result;
        } catch (Throwable $e) {
            Log::warning('Maintenance fetchTechnicalStaff fallback', ['e' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ambil daftar perangkat riil berdasarkan device_type untuk memudahkan pemilihan perangkat & auto-lokasi.
     */
    public function getDeviceOptionsProperty(): array
    {
        $type = $this->showEditModal
            ? ($this->editMaint['device_type'] ?? 'router')
            : ($this->newMaint['device_type'] ?? 'router');

        return $this->loadDevicesByType((string) $type);
    }

    protected function loadDevicesByType(string $type): array
    {
        try {
            return match ($type) {
                'router' => RouterModel::withoutGlobalScopes()
                    ->with('pop')
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($r) => [
                        'key' => (string) $r->id,
                        'name' => $r->name . ($r->ip_address ? ' (' . $r->ip_address . ')' : ''),
                        'location' => $r->pop?->name ?? '',
                    ])->all(),
                'olt' => Olt::withoutGlobalScopes()
                    ->with('pop')
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($o) => [
                        'key' => (string) $o->id,
                        'name' => $o->name . ($o->ip_address ? ' (' . $o->ip_address . ')' : ''),
                        'location' => $o->pop?->name ?: ($o->address ?? ''),
                    ])->all(),
                'onu' => Onu::withoutGlobalScopes()
                    ->with('olt')
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($n) => [
                        'key' => (string) $n->id,
                        'name' => ($n->name ?: 'ONU') . ($n->serial_number ? ' - ' . $n->serial_number : ''),
                        'location' => $n->olt?->name ?? '',
                    ])->all(),
                'odp' => Odp::withoutGlobalScopes()
                    ->orderBy('name')
                    ->limit(200)
                    ->get()
                    ->map(fn ($d) => [
                        'key' => (string) $d->id,
                        'name' => $d->name . ($d->code ? ' [' . $d->code . ']' : ''),
                        'location' => $d->address ?? '',
                    ])->all(),
                'odc' => Odc::withoutGlobalScopes()
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($c) => [
                        'key' => (string) $c->id,
                        'name' => $c->name . ($c->code ? ' [' . $c->code . ']' : ''),
                        'location' => $c->address ?? '',
                    ])->all(),
                'pop' => Pop::query()
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($p) => [
                        'key' => (string) $p->id,
                        'name' => $p->name . ($p->code ? ' [' . $p->code . ']' : ''),
                        'location' => $p->address ?: ($p->city ?? ''),
                    ])->all(),
                'tower' => Tower::query()
                    ->orderBy('name')
                    ->limit(150)
                    ->get()
                    ->map(fn ($t) => [
                        'key' => (string) $t->id,
                        'name' => $t->name,
                        'location' => $t->address ?? '',
                    ])->all(),
                default => [],
            };
        } catch (Throwable) {
            return [];
        }
    }

    public function updatedNewMaintDeviceType(): void
    {
        $this->newMaintDeviceSelect = '';
    }

    public function updatedNewMaintDeviceSelect($val): void
    {
        if (!$val) {
            return;
        }
        foreach ($this->loadDevicesByType((string) ($this->newMaint['device_type'] ?? 'router')) as $item) {
            if ((string) $item['key'] === (string) $val) {
                $this->newMaint['device_name'] = $item['name'];
                if (!empty($item['location'])) {
                    $this->newMaint['location_name'] = $item['location'];
                }
                break;
            }
        }
    }

    public function updatedEditMaintDeviceType(): void
    {
        $this->editMaintDeviceSelect = '';
    }

    public function updatedEditMaintDeviceSelect($val): void
    {
        if (!$val) {
            return;
        }
        foreach ($this->loadDevicesByType((string) ($this->editMaint['device_type'] ?? 'router')) as $item) {
            if ((string) $item['key'] === (string) $val) {
                $this->editMaint['device_name'] = $item['name'];
                if (!empty($item['location'])) {
                    $this->editMaint['location_name'] = $item['location'];
                }
                break;
            }
        }
    }

    public function updatedUsageFormMaterialId($val): void
    {
        if (!$val || !Schema::hasTable('support_maintenance_materials')) {
            return;
        }
        $mat = DB::table('support_maintenance_materials')->where('id', (int) $val)->first();
        if ($mat) {
            $this->usageForm['material_name'] = $mat->name;
            $this->usageForm['unit'] = $mat->unit ?: 'pcs';
        }
    }

    public function updatedUsageFormTechnicianId($val): void
    {
        if (!$val) {
            return;
        }
        $u = User::find((int) $val);
        if ($u) {
            $this->usageForm['technician_name'] = $u->name;
        }
    }

    public function loadAll(): void
    {
        try {
            $q = DB::table('support_maintenances');
            $this->summary = [
                'total' => (clone $q)->count(),
                'scheduled' => (clone $q)->where('status', 'scheduled')->count(),
                'in_progress' => (clone $q)->where('status', 'in_progress')->count(),
                'completed' => (clone $q)->where('status', 'completed')->count(),
                'preventive' => (clone $q)->where('type', 'preventive')->count(),
                'corrective' => (clone $q)->where('type', 'corrective')->count(),
                'overdue' => (clone $q)->whereDate('scheduled_date', '<', now()->toDateString())
                    ->whereIn('status', ['scheduled', 'in_progress'])->count(),
            ];
        } catch (Throwable $e) {
            Log::error('Maintenance summary failed', ['e' => $e->getMessage()]);
            $this->summary = [
                'total' => 0, 'scheduled' => 0, 'in_progress' => 0, 'completed' => 0,
                'preventive' => 0, 'corrective' => 0, 'overdue' => 0,
            ];
        }

        $techs = $this->fetchTechnicalStaff();

        $this->filterOptions = [
            'statuses' => [
                'scheduled' => 'Dijadwalkan',
                'in_progress' => 'Sedang Dikerjakan',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ],
            'types' => [
                'preventive' => 'Preventive (Pencegahan)',
                'corrective' => 'Corrective (Perbaikan)',
                'predictive' => 'Predictive (Prediksi)',
                'emergency' => 'Emergency (Darurat)',
            ],
            'technicians' => $techs,
            'device_types' => [
                'router' => 'Router / Mikrotik',
                'olt' => 'OLT (Fiber)',
                'onu' => 'ONU / ONT',
                'odp' => 'ODP (Optical Distribution Point)',
                'odc' => 'ODC (Optical Distribution Cabinet)',
                'pop' => 'POP / Hub Wilayah',
                'tower' => 'Tower / Tiang Utama',
                'server' => 'Server / Data Center',
                'other' => 'Lainnya',
            ],
            'priorities' => [
                'low' => 'Rendah (Low)',
                'medium' => 'Normal (Medium)',
                'high' => 'Tinggi (High)',
                'critical' => 'Kritis / Mendesak (Critical)',
            ],
        ];

        try {
            $this->maintenanceScheduleOptions = DB::table('support_maintenances')
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id', 'title', 'scheduled_date', 'status'])
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'label' => '#MT-' . $r->id . ' - ' . Str::limit((string) $r->title, 42) . ' (' . ($r->scheduled_date ?: '-') . ')',
                ])->all();
        } catch (Throwable) {
            $this->maintenanceScheduleOptions = [];
        }

        $this->loadCalendar();
        $this->loadTechnicianStats();
        $this->loadMaterial();
    }

    public function prevMonth(): void
    {
        $dt = Carbon::parse(($this->calendarMonth ?: now()->format('Y-m')) . '-01')->subMonth();
        $this->calendarMonth = $dt->format('Y-m');
        $this->loadCalendar();
    }

    public function nextMonth(): void
    {
        $dt = Carbon::parse(($this->calendarMonth ?: now()->format('Y-m')) . '-01')->addMonth();
        $this->calendarMonth = $dt->format('Y-m');
        $this->loadCalendar();
    }

    protected function loadCalendar(): void
    {
        try {
            $month = $this->calendarMonth ?: now()->format('Y-m');
            $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $rows = DB::table('support_maintenances')
                ->leftJoin('users', 'support_maintenances.assigned_to', '=', 'users.id')
                ->select(['support_maintenances.*', 'users.name as technician_name'])
                ->whereBetween('support_maintenances.scheduled_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->orderBy('support_maintenances.scheduled_time')
                ->get();

            $eventsByDate = [];
            foreach ($rows as $r) {
                $d = $r->scheduled_date ?: substr((string) $r->created_at, 0, 10);
                $eventsByDate[$d][] = [
                    'id' => $r->id,
                    'title' => $r->title ?? ('Maintenance #' . $r->id),
                    'time' => $r->scheduled_time ?? '09:00',
                    'type' => $r->type ?? 'preventive',
                    'status' => $r->status ?? 'scheduled',
                    'priority' => $r->priority ?? 'medium',
                    'technician' => $r->technician_name ?? null,
                ];
            }

            $calendar = [];
            $startGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
            $endGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

            $curr = $startGrid->copy();
            while ($curr->lte($endGrid)) {
                $dStr = $curr->toDateString();
                $calendar[] = [
                    'day' => $curr->day,
                    'date' => $dStr,
                    'is_today' => $curr->isToday(),
                    'is_current_month' => $curr->month === $startOfMonth->month,
                    'events' => $eventsByDate[$dStr] ?? [],
                ];
                $curr->addDay();
            }

            $this->calendar = $calendar;
            $this->calendarEvents = $eventsByDate;
        } catch (Throwable $e) {
            Log::error('Maintenance calendar failed', ['e' => $e->getMessage()]);
            $this->calendar = [];
            $this->calendarEvents = [];
        }
    }

    /**
     * Beban Teknisi: Hanya menampilkan staf teknis (TECHNICIAN / NOC) atau yang ditugaskan di support_maintenances,
     * menggunakan single aggregated query (tanpa N+1 loop ke seluruh tabel users).
     */
    protected function loadTechnicianStats(): void
    {
        try {
            $assignedIds = DB::table('support_maintenances')
                ->whereNotNull('assigned_to')
                ->distinct()
                ->pluck('assigned_to')
                ->map(fn ($id) => (int) $id)
                ->all();

            $techUsers = User::query()
                ->with('roles')
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['customer', 'reseller']);
                })
                ->whereNull('reseller_id')
                ->where(function ($q) use ($assignedIds) {
                    $q->where(function ($sub) {
                        $sub->where('is_active', true)
                            ->whereHas('roles', fn ($rq) => $rq->where('name', 'manager'))
                            ->whereNotIn(DB::raw("UPPER(COALESCE(job_function, ''))"), ['CASHIER', 'FINANCE', 'ACCOUNTING', 'HR']);
                    })->orWhere(function ($sub) {
                        $sub->where('is_active', true)
                            ->whereIn(DB::raw("UPPER(COALESCE(job_function, ''))"), ['TECHNICIAN', 'NOC']);
                    });
                    if (!empty($assignedIds)) {
                        $q->orWhereIn('id', $assignedIds);
                    }
                })
                ->orderByRaw("CASE WHEN UPPER(COALESCE(job_function, '')) = 'TECHNICIAN' THEN 0 WHEN UPPER(COALESCE(job_function, '')) = 'NOC' THEN 1 ELSE 2 END")
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'job_function', 'job_title', 'wilayah']);

            if ($techUsers->isEmpty()) {
                $this->technicianStats = [];
                $this->technicianLoad = [];
                return;
            }

            $userIds = $techUsers->pluck('id')->all();
            $today = now()->toDateString();

            // Aggregated Maintenance Counts (1 query)
            $maintAgg = DB::table('support_maintenances')
                ->whereIn('assigned_to', $userIds)
                ->selectRaw("
                    assigned_to,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN scheduled_date < ? AND status IN ('scheduled', 'in_progress') THEN 1 ELSE 0 END) as overdue
                ", [$today])
                ->groupBy('assigned_to')
                ->get()
                ->keyBy('assigned_to');

            // Aggregated Active Support Tickets (1 query)
            $ticketAgg = [];
            if (Schema::hasTable('tickets')) {
                $ticketAgg = DB::table('tickets')
                    ->whereIn('assigned_to', $userIds)
                    ->whereIn('status', ['open', 'in_progress', 'pending_customer'])
                    ->whereNull('deleted_at')
                    ->selectRaw('assigned_to, COUNT(*) as active_tickets')
                    ->groupBy('assigned_to')
                    ->pluck('active_tickets', 'assigned_to')
                    ->all();
            }

            // Today's Attendance Status (1 query)
            $presentKeys = [];
            if (Schema::hasTable('attendances')) {
                $attRows = DB::table('attendances')
                    ->whereDate('date', $today)
                    ->whereNotNull('checked_in_at')
                    ->pluck('technician_id')
                    ->map(fn ($v) => (string) $v)
                    ->all();
                $presentKeys = array_flip($attRows);
            }

            $stats = [];
            foreach ($techUsers as $t) {
                $agg = $maintAgg->get($t->id);
                $all = (int) ($agg->total ?? 0);
                $scheduled = (int) ($agg->scheduled ?? 0);
                $inProgress = (int) ($agg->in_progress ?? 0);
                $completed = (int) ($agg->completed ?? 0);
                $overdue = (int) ($agg->overdue ?? 0);
                $activeTickets = (int) ($ticketAgg[$t->id] ?? 0);

                $jf = strtoupper((string) ($t->job_function ?? ''));
                $spec = match ($jf) {
                    'TECHNICIAN' => 'Teknisi Lapangan' . ($t->wilayah ? ' · ' . $t->wilayah : ''),
                    'NOC' => 'NOC & Infrastruktur' . ($t->wilayah ? ' · ' . $t->wilayah : ''),
                    default => $t->job_title ?: 'Staf Teknis Jaringan',
                };

                $isOnDuty = isset($presentKeys[(string) $t->id]) || (!empty($t->uuid) && isset($presentKeys[(string) $t->uuid]));

                $stats[] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'specialization' => $spec,
                    'on_duty' => $isOnDuty,
                    'total' => $all,
                    'scheduled' => $scheduled,
                    'in_progress' => $inProgress,
                    'completed' => $completed,
                    'overdue' => $overdue,
                    'active_tickets' => $activeTickets,
                    'rate' => $all > 0 ? round(($completed / $all) * 100, 1) : 0,
                ];
            }

            $this->technicianStats = $stats;
            $this->technicianLoad = $stats;
        } catch (Throwable $e) {
            Log::error('Maintenance technician stats failed', ['e' => $e->getMessage()]);
            $this->technicianStats = [];
            $this->technicianLoad = [];
        }
    }

    public function filterByTechnician(int $techId): void
    {
        $this->filters['technician_id'] = (string) $techId;
        $this->showFilters = true;
        $this->activeTab = 'history';
        $this->resetPage();
    }

    /**
     * Material & Stok: 100% terhubung ke database support_maintenance_materials & support_maintenance_usages,
     * ditambah ringkasan aset gudang (assets & warehouses).
     */
    protected function loadMaterial(): void
    {
        try {
            if (Schema::hasTable('warehouses')) {
                $this->warehouseOptions = DB::table('warehouses')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();
            }
        } catch (Throwable) {
            $this->warehouseOptions = [];
        }

        try {
            if (Schema::hasTable('assets')) {
                $aq = DB::table('assets');
                if (Schema::hasColumn('assets', 'deleted_at')) {
                    $aq->whereNull('deleted_at');
                }
                $this->inventoryAssetSummary = [
                    'total' => (clone $aq)->count(),
                    'available' => Schema::hasColumn('assets', 'status')
                        ? (clone $aq)->whereIn('status', ['available', 'in_stock', 'ready'])->count()
                        : (clone $aq)->count(),
                ];
            }
        } catch (Throwable) {
            $this->inventoryAssetSummary = ['total' => 0, 'available' => 0];
        }

        $stockRows = [];
        if (Schema::hasTable('support_maintenance_materials')) {
            try {
                $q = DB::table('support_maintenance_materials')->orderBy('name');
                if ($this->search && $this->activeTab === 'material') {
                    $s = '%' . $this->search . '%';
                    $q->where(function ($sub) use ($s) {
                        $sub->where('name', 'like', $s)
                            ->orWhere('sku', 'like', $s)
                            ->orWhere('location', 'like', $s)
                            ->orWhere('category', 'like', $s);
                    });
                }
                foreach ($q->get() as $m) {
                    $qty = (int) ($m->stock ?? 0);
                    $min = (int) ($m->min_stock ?? 0);
                    $stockRows[] = [
                        'id' => (int) $m->id,
                        'name' => $m->name,
                        'sku' => $m->sku ?: ('MAT-' . str_pad((string) $m->id, 3, '0', STR_PAD_LEFT)),
                        'category' => $m->category ?: 'Umum',
                        'stock' => $qty,
                        'qty' => $qty,
                        'unit' => $m->unit ?: 'pcs',
                        'min_stock' => $min,
                        'is_low' => $qty <= $min,
                        'location' => $m->location ?: 'Gudang Utama',
                        'notes' => $m->notes,
                    ];
                }
            } catch (Throwable $e) {
                Log::error('Maintenance loadMaterial stock failed', ['e' => $e->getMessage()]);
            }
        }

        $usageRows = [];
        if (Schema::hasTable('support_maintenance_usages')) {
            try {
                $uq = DB::table('support_maintenance_usages')
                    ->leftJoin('support_maintenances', 'support_maintenance_usages.maintenance_id', '=', 'support_maintenances.id')
                    ->select([
                        'support_maintenance_usages.*',
                        'support_maintenances.title as maintenance_title',
                    ])
                    ->orderByDesc('support_maintenance_usages.used_date')
                    ->orderByDesc('support_maintenance_usages.id')
                    ->limit(50);

                if ($this->search && $this->activeTab === 'material') {
                    $s = '%' . $this->search . '%';
                    $uq->where(function ($sub) use ($s) {
                        $sub->where('support_maintenance_usages.material_name', 'like', $s)
                            ->orWhere('support_maintenance_usages.technician_name', 'like', $s)
                            ->orWhere('support_maintenances.title', 'like', $s);
                    });
                }

                foreach ($uq->get() as $u) {
                    $usageRows[] = [
                        'id' => (int) $u->id,
                        'date' => $u->used_date
                            ? Carbon::parse($u->used_date)->format('d M Y')
                            : Carbon::parse($u->created_at)->format('d M Y'),
                        'wo_ref' => $u->maintenance_id
                            ? ('#MT-' . $u->maintenance_id . ($u->maintenance_title ? ' (' . Str::limit($u->maintenance_title, 26) . ')' : ''))
                            : 'Umum / Langsung',
                        'material' => $u->material_name,
                        'qty' => (int) $u->quantity,
                        'unit' => $u->unit ?: 'pcs',
                        'technician' => $u->technician_name ?: '-',
                        'notes' => $u->notes,
                    ];
                }
            } catch (Throwable $e) {
                Log::error('Maintenance loadMaterial usage failed', ['e' => $e->getMessage()]);
            }
        }

        $this->materialStock = $stockRows;
        $this->materialUsage = $usageRows;
        $this->stock = $stockRows;
        $this->usage = $usageRows;
    }

    public function updatedCalendarMonth(): void
    {
        if ($this->activeTab === 'calendar') {
            $this->loadCalendar();
        }
    }

    public function getRowsQuery()
    {
        return $this->buildQuery()->limit(min(500, $this->perPage * 10));
    }

    protected function buildQuery()
    {
        $q = DB::table('support_maintenances')
            ->leftJoin('users', 'support_maintenances.assigned_to', '=', 'users.id')
            ->select([
                'support_maintenances.*',
                'users.name as technician_name',
            ]);

        if (!empty($this->filters['status'])) {
            $q->where('support_maintenances.status', $this->filters['status']);
        }
        if (!empty($this->filters['type'])) {
            $q->where('support_maintenances.type', $this->filters['type']);
        }
        if (!empty($this->filters['technician_id'])) {
            $q->where('support_maintenances.assigned_to', $this->filters['technician_id']);
        }
        if (!empty($this->filters['device_type'])) {
            $q->where('support_maintenances.device_type', $this->filters['device_type']);
        }
        if (!empty($this->filters['scheduled_from'])) {
            $q->whereDate('support_maintenances.scheduled_date', '>=', $this->filters['scheduled_from']);
        }
        if (!empty($this->filters['scheduled_to'])) {
            $q->whereDate('support_maintenances.scheduled_date', '<=', $this->filters['scheduled_to']);
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $q->where(function ($qq) use ($s) {
                $qq->where('support_maintenances.title', 'like', $s)
                    ->orWhere('support_maintenances.device_name', 'like', $s)
                    ->orWhere('support_maintenances.location_name', 'like', $s)
                    ->orWhere('support_maintenances.notes', 'like', $s)
                    ->orWhere('support_maintenances.resolution_notes', 'like', $s)
                    ->orWhere('users.name', 'like', $s);
            });
        }

        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $field = in_array($this->sortField, ['id', 'status', 'priority', 'scheduled_date', 'created_at', 'completed_at'], true)
            ? 'support_maintenances.' . $this->sortField
            : 'support_maintenances.id';
        $q->orderBy($field, $dir);

        return $q;
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->buildQuery()->paginate($this->perPage);
        }, 'Gagal memuat data Maintenance');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        try {
            return match ($action) {
                'complete' => (function () use ($ids) {
                    $n = DB::table('support_maintenances')->whereIn('id', $ids)->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
                    $this->loadAll();
                    return $n;
                })(),
                'export' => $this->doBulkExport($ids),
                default => 0,
            };
        } catch (Throwable $e) {
            Log::error('Maintenance bulk action failed', ['e' => $e->getMessage()]);
            return 0;
        }
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = DB::table('support_maintenances')
            ->leftJoin('users', 'support_maintenances.assigned_to', '=', 'users.id')
            ->select(['support_maintenances.*', 'users.name as technician_name'])
            ->whereIn('support_maintenances.id', $ids)->get()->all();
        $this->exportCsvDirect($rows);
        return count($ids);
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $rows = $this->buildQuery()->limit(5000)->get()->all();
            return $this->buildCsv($rows, 'maintenance_' . now()->format('Ymd') . '.csv');
        } catch (Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return back();
        }
    }

    public function exportCsvDirect(array $rows): void
    {
        $resp = $this->buildCsv($rows, 'maintenance_' . now()->format('Ymd_His') . '.csv');
        $resp->send();
        exit;
    }

    protected function buildCsv(array $rows, string $filename): StreamedResponse
    {
        $headers = ['ID', 'Judul Maintenance', 'Jenis', 'Perangkat', 'Lokasi', 'Tgl Jadwal', 'Jam', 'Teknisi', 'Prioritas', 'Status', 'Catatan Penyelesaian'];
        return response()->stream(function () use ($rows, $headers) {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, $headers);
            foreach ($rows as $r) {
                $data = (array) $r;
                fputcsv($fh, [
                    $data['id'] ?? '',
                    $data['title'] ?? '',
                    $data['type'] ?? '',
                    $data['device_name'] ?? '',
                    $data['location_name'] ?? '',
                    $data['scheduled_date'] ?? '',
                    $data['scheduled_time'] ?? '',
                    $data['technician_name'] ?? '',
                    $data['priority'] ?? '',
                    $data['status'] ?? '',
                    $data['resolution_notes'] ?? '',
                ]);
            }
            fclose($fh);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function notifyAssignedTechnician(?int $techId, int $maintId, string $title, ?string $schedDate, ?string $schedTime): void
    {
        if (!$techId || !Schema::hasTable('notifications')) {
            return;
        }

        try {
            Notification::create([
                'uuid' => (string) Str::uuid(),
                'recipient_id' => $techId,
                'title' => 'Penugasan Maintenance #MT-' . $maintId,
                'message' => "Anda ditugaskan untuk pekerjaan maintenance: {$title} (Jadwal: " . ($schedDate ?: '-') . ' ' . ($schedTime ?: '09:00') . ').',
                'type' => 'maintenance_assigned',
                'status' => 'unread',
                'sent_at' => now(),
                'data' => [
                    'maintenance_id' => $maintId,
                    'url' => route('technician.my-jobs.maintenance'),
                ],
                'created_by' => Auth::id(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed sending maintenance notification', ['e' => $e->getMessage()]);
        }
    }

    public function openCreate(): void
    {
        $this->errorMessage = null;
        $this->resetNewMaintForm();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
    }

    public function submitCreate(): void
    {
        if (empty(trim((string) ($this->newMaint['title'] ?? '')))) {
            $this->errorMessage = 'Judul pekerjaan maintenance wajib diisi.';
            return;
        }

        try {
            $uid = Auth::id() ?? 1;
            $assignedTo = !empty($this->newMaint['technician_id']) ? (int) $this->newMaint['technician_id'] : null;
            $schedDate = $this->newMaint['scheduled_date'] ?: now()->toDateString();
            $schedTime = $this->newMaint['scheduled_time'] ?: '09:00';

            $insertData = [
                'title' => trim((string) $this->newMaint['title']),
                'type' => $this->newMaint['type'] ?? 'preventive',
                'status' => 'scheduled',
                'priority' => $this->newMaint['priority'] ?? 'medium',
                'device_type' => $this->newMaint['device_type'] ?? 'router',
                'device_name' => trim((string) ($this->newMaint['device_name'] ?? '')) ?: null,
                'location_name' => trim((string) ($this->newMaint['location_name'] ?? '')) ?: null,
                'scheduled_date' => $schedDate,
                'scheduled_time' => $schedTime,
                'assigned_to' => $assignedTo,
                'notes' => trim((string) ($this->newMaint['notes'] ?? '')),
                'created_by' => $uid,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('support_maintenances', 'branch_id') && Auth::user()?->branch_id) {
                $insertData['branch_id'] = Auth::user()->branch_id;
            }

            $id = DB::table('support_maintenances')->insertGetId($insertData);

            $this->notifyAssignedTechnician($assignedTo, (int) $id, $insertData['title'], $schedDate, $schedTime);

            session()->flash('success', 'Jadwal Pemeliharaan #MT-' . $id . ' berhasil dibuat.');
            $this->showCreateModal = false;
            $this->loadAll();
            $this->resetPage();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal membuat jadwal maintenance: ' . $e->getMessage();
        }
    }

    public function openEdit(int $id): void
    {
        $row = DB::table('support_maintenances')->where('id', $id)->first();
        if (!$row) {
            return;
        }

        $this->errorMessage = null;
        $this->editMaintDeviceSelect = '';
        $this->editMaint = [
            'id' => (int) $row->id,
            'title' => (string) ($row->title ?? ''),
            'type' => (string) ($row->type ?? 'preventive'),
            'status' => (string) ($row->status ?? 'scheduled'),
            'priority' => (string) ($row->priority ?? 'medium'),
            'device_type' => (string) ($row->device_type ?? 'router'),
            'device_name' => (string) ($row->device_name ?? ''),
            'location_name' => (string) ($row->location_name ?? ''),
            'scheduled_date' => (string) ($row->scheduled_date ?? now()->toDateString()),
            'scheduled_time' => (string) ($row->scheduled_time ?? '09:00'),
            'technician_id' => $row->assigned_to ? (string) $row->assigned_to : '',
            'notes' => (string) ($row->notes ?? ''),
            'resolution_notes' => (string) ($row->resolution_notes ?? ''),
        ];
        $this->showEditModal = true;
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
    }

    public function submitEdit(): void
    {
        $id = (int) ($this->editMaint['id'] ?? 0);
        if (!$id) {
            return;
        }

        if (empty(trim((string) ($this->editMaint['title'] ?? '')))) {
            $this->errorMessage = 'Judul pekerjaan maintenance wajib diisi.';
            return;
        }

        try {
            $existing = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$existing) {
                return;
            }

            $newTechId = !empty($this->editMaint['technician_id']) ? (int) $this->editMaint['technician_id'] : null;
            $newStatus = $this->editMaint['status'] ?? 'scheduled';

            $completedAt = $existing->completed_at;
            if ($newStatus === 'completed' && empty($completedAt)) {
                $completedAt = now();
            } elseif ($newStatus !== 'completed') {
                $completedAt = null;
            }

            $updateData = [
                'title' => trim((string) $this->editMaint['title']),
                'type' => $this->editMaint['type'] ?? 'preventive',
                'status' => $newStatus,
                'priority' => $this->editMaint['priority'] ?? 'medium',
                'device_type' => $this->editMaint['device_type'] ?? 'router',
                'device_name' => trim((string) ($this->editMaint['device_name'] ?? '')) ?: null,
                'location_name' => trim((string) ($this->editMaint['location_name'] ?? '')) ?: null,
                'scheduled_date' => $this->editMaint['scheduled_date'] ?: now()->toDateString(),
                'scheduled_time' => $this->editMaint['scheduled_time'] ?: '09:00',
                'assigned_to' => $newTechId,
                'notes' => trim((string) ($this->editMaint['notes'] ?? '')),
                'resolution_notes' => trim((string) ($this->editMaint['resolution_notes'] ?? '')) ?: null,
                'completed_at' => $completedAt,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ];

            DB::table('support_maintenances')->where('id', $id)->update($updateData);

            if ($newTechId && (int) ($existing->assigned_to ?? 0) !== $newTechId) {
                $this->notifyAssignedTechnician(
                    $newTechId,
                    $id,
                    $updateData['title'],
                    $updateData['scheduled_date'],
                    $updateData['scheduled_time']
                );
            }

            session()->flash('success', "Jadwal Maintenance #MT-{$id} berhasil diperbarui.");
            $this->showEditModal = false;
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal memperbarui jadwal: ' . $e->getMessage();
        }
    }

    // --- Material Stock CRUD ---
    public function openMaterialModal(): void
    {
        $this->errorMessage = null;
        $this->resetMaterialForm();
        $this->showMaterialModal = true;
    }

    public function editMaterial(int $id): void
    {
        if (!Schema::hasTable('support_maintenance_materials')) {
            return;
        }
        $row = DB::table('support_maintenance_materials')->where('id', $id)->first();
        if (!$row) {
            return;
        }

        $this->errorMessage = null;
        $this->editingMaterialId = (int) $row->id;
        $this->materialForm = [
            'sku' => (string) ($row->sku ?? ''),
            'name' => (string) ($row->name ?? ''),
            'category' => (string) ($row->category ?? 'Fiber Optic'),
            'stock' => (int) ($row->stock ?? 0),
            'min_stock' => (int) ($row->min_stock ?? 5),
            'unit' => (string) ($row->unit ?? 'pcs'),
            'location' => (string) ($row->location ?? 'Gudang Utama'),
            'notes' => (string) ($row->notes ?? ''),
        ];
        $this->showMaterialModal = true;
    }

    public function closeMaterialModal(): void
    {
        $this->showMaterialModal = false;
    }

    public function saveMaterial(): void
    {
        if (!Schema::hasTable('support_maintenance_materials')) {
            $this->errorMessage = 'Tabel stok material belum tersedia. Jalankan migrasi terlebih dahulu.';
            return;
        }

        $name = trim((string) ($this->materialForm['name'] ?? ''));
        if ($name === '') {
            $this->errorMessage = 'Nama material wajib diisi.';
            return;
        }

        try {
            $payload = [
                'sku' => trim((string) ($this->materialForm['sku'] ?? '')) ?: null,
                'name' => $name,
                'category' => trim((string) ($this->materialForm['category'] ?? 'Fiber Optic')) ?: 'Umum',
                'stock' => max(0, (int) ($this->materialForm['stock'] ?? 0)),
                'min_stock' => max(0, (int) ($this->materialForm['min_stock'] ?? 0)),
                'unit' => trim((string) ($this->materialForm['unit'] ?? 'pcs')) ?: 'pcs',
                'location' => trim((string) ($this->materialForm['location'] ?? 'Gudang Utama')) ?: 'Gudang Utama',
                'notes' => trim((string) ($this->materialForm['notes'] ?? '')) ?: null,
                'updated_at' => now(),
            ];

            if ($this->editingMaterialId) {
                DB::table('support_maintenance_materials')
                    ->where('id', $this->editingMaterialId)
                    ->update($payload);
                session()->flash('success', "Data stok material \"{$name}\" berhasil diperbarui.");
            } else {
                $payload['branch_id'] = Auth::user()?->branch_id;
                $payload['created_at'] = now();
                DB::table('support_maintenance_materials')->insert($payload);
                session()->flash('success', "Material baru \"{$name}\" berhasil ditambahkan ke stok.");
            }

            $this->showMaterialModal = false;
            $this->loadMaterial();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal menyimpan material: ' . $e->getMessage();
        }
    }

    public function deleteMaterial(int $id): void
    {
        $this->confirmTitle = 'Hapus Item Stok Material';
        $this->confirmMessage = 'Yakin ingin menghapus item stok material ini dari daftar?';
        $this->confirmAction = 'delete-material';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Hapus';
        $this->confirmBtnClass = 'bg-rose-600 hover:bg-rose-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    // --- Material Usage Logging (with Atomic Stock Deduction) ---
    public function openUsageModal(): void
    {
        $this->errorMessage = null;
        $this->resetUsageForm();
        $this->usageForm['technician_name'] = Auth::user()?->name ?? '';
        $this->showUsageModal = true;
    }

    public function closeUsageModal(): void
    {
        $this->showUsageModal = false;
    }

    public function submitUsage(): void
    {
        if (!Schema::hasTable('support_maintenance_usages')) {
            $this->errorMessage = 'Tabel riwayat pemakaian material belum tersedia. Jalankan migrasi terlebih dahulu.';
            return;
        }

        $matId = !empty($this->usageForm['material_id']) ? (int) $this->usageForm['material_id'] : null;
        $matName = trim((string) ($this->usageForm['material_name'] ?? ''));
        $qty = (int) ($this->usageForm['quantity'] ?? 0);

        if ($matId && $matName === '' && Schema::hasTable('support_maintenance_materials')) {
            $m = DB::table('support_maintenance_materials')->where('id', $matId)->first();
            if ($m) {
                $matName = (string) $m->name;
                $this->usageForm['unit'] = $m->unit ?: 'pcs';
            }
        }

        if ($matName === '' || $qty <= 0) {
            $this->errorMessage = 'Pilih atau isi nama material dan jumlah kuantitas minimal 1.';
            return;
        }

        $techId = !empty($this->usageForm['technician_id']) ? (int) $this->usageForm['technician_id'] : null;
        $techName = trim((string) ($this->usageForm['technician_name'] ?? ''));
        if ($techId && $techName === '') {
            $techName = User::find($techId)?->name ?? '';
        }
        if ($techName === '') {
            $techName = Auth::user()?->name ?? 'Teknisi';
        }

        try {
            DB::transaction(function () use ($matId, $matName, $qty, $techId, $techName) {
                if ($matId && Schema::hasTable('support_maintenance_materials')) {
                    $matRow = DB::table('support_maintenance_materials')
                        ->where('id', $matId)
                        ->lockForUpdate()
                        ->first();

                    if (!$matRow) {
                        throw new \RuntimeException('Material stok tidak ditemukan.');
                    }

                    if ((int) $matRow->stock < $qty) {
                        throw new \RuntimeException("Stok \"{$matRow->name}\" tidak mencukupi (tersisa {$matRow->stock} {$matRow->unit}).");
                    }

                    DB::table('support_maintenance_materials')
                        ->where('id', $matId)
                        ->update([
                            'stock' => (int) $matRow->stock - $qty,
                            'updated_at' => now(),
                        ]);
                }

                DB::table('support_maintenance_usages')->insert([
                    'maintenance_id' => !empty($this->usageForm['maintenance_id']) ? (int) $this->usageForm['maintenance_id'] : null,
                    'material_id' => $matId,
                    'material_name' => $matName,
                    'quantity' => $qty,
                    'unit' => trim((string) ($this->usageForm['unit'] ?? 'pcs')) ?: 'pcs',
                    'technician_id' => $techId,
                    'technician_name' => $techName,
                    'used_date' => $this->usageForm['used_date'] ?: now()->toDateString(),
                    'notes' => trim((string) ($this->usageForm['notes'] ?? '')) ?: null,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            session()->flash('success', "Pemakaian {$qty} {$this->usageForm['unit']} {$matName} berhasil dicatat" . ($matId ? ' & stok otomatis dikurangi.' : '.'));
            $this->closeUsageModal();
            $this->loadMaterial();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function deleteUsage(int $id): void
    {
        $this->confirmTitle = 'Batalkan Catatan Pemakaian';
        $this->confirmMessage = 'Membatalkan catatan ini akan mengembalikan jumlah stok ke gudang material (jika terhubung ke item stok). Lanjutkan?';
        $this->confirmAction = 'delete-usage';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Batalkan & Kembalikan Stok';
        $this->confirmBtnClass = 'bg-amber-600 hover:bg-amber-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    // --- Row Actions ---
    public function rowStart(int $id): void
    {
        try {
            DB::table('support_maintenances')->where('id', $id)->update([
                'status' => 'in_progress',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
            session()->flash('success', 'Maintenance #MT-' . $id . ' sedang dikerjakan.');
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal memulai: ' . $e->getMessage();
        }
    }

    public function rowComplete(int $id): void
    {
        $row = DB::table('support_maintenances')->where('id', $id)->first();
        if (!$row) {
            return;
        }
        $this->completeMaintId = (int) $row->id;
        $this->completeMaintTitle = (string) ($row->title ?? ('Maintenance #' . $row->id));
        $this->completeResolutionNotes = (string) ($row->resolution_notes ?? '');
        $this->showCompleteModal = true;
    }

    public function submitComplete(): void
    {
        if (!$this->completeMaintId) {
            return;
        }

        try {
            $id = $this->completeMaintId;
            $note = trim($this->completeResolutionNotes) !== ''
                ? trim($this->completeResolutionNotes)
                : 'Pekerjaan pemeliharaan selesai diverifikasi.';

            DB::table('support_maintenances')->where('id', $id)->update([
                'status' => 'completed',
                'completed_at' => now(),
                'resolution_notes' => $note,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            $this->showCompleteModal = false;
            $this->completeMaintId = null;
            $this->completeResolutionNotes = '';
            session()->flash('success', "Maintenance #MT-{$id} telah diselesaikan.");
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal menyelesaikan: ' . $e->getMessage();
        }
    }

    public function rowDelete(int $id): void
    {
        $this->confirmTitle = 'Hapus Jadwal #MT-' . $id;
        $this->confirmMessage = 'Yakin ingin menghapus jadwal pemeliharaan ini? Tindakan ini tidak dapat dibatalkan.';
        $this->confirmAction = 'delete-maint';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Hapus';
        $this->confirmBtnClass = 'bg-rose-600 hover:bg-rose-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function rowDetail(int $id): void
    {
        try {
            $row = DB::table('support_maintenances')
                ->leftJoin('users', 'support_maintenances.assigned_to', '=', 'users.id')
                ->select(['support_maintenances.*', 'users.name as technician_name'])
                ->where('support_maintenances.id', $id)
                ->first();

            if (!$row) {
                return;
            }

            $this->detailRow = (array) $row;
            $this->detailUsages = [];

            if (Schema::hasTable('support_maintenance_usages')) {
                $this->detailUsages = DB::table('support_maintenance_usages')
                    ->where('maintenance_id', $id)
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn ($u) => (array) $u)
                    ->all();
            }

            $this->showDetailModal = true;
        } catch (Throwable) {
            // Silently ignore
        }
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'delete-maint') {
            try {
                $id = (int) ($this->confirmParams['id'] ?? 0);
                DB::table('support_maintenances')->where('id', $id)->delete();
                session()->flash('success', 'Jadwal Maintenance #MT-' . $id . ' telah dihapus.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal menghapus: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }

        if ($this->confirmAction === 'delete-material') {
            try {
                $id = (int) ($this->confirmParams['id'] ?? 0);
                if (Schema::hasTable('support_maintenance_materials')) {
                    DB::table('support_maintenance_materials')->where('id', $id)->delete();
                    session()->flash('success', 'Item stok material berhasil dihapus.');
                    $this->loadMaterial();
                }
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal menghapus material: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }

        if ($this->confirmAction === 'delete-usage') {
            try {
                $id = (int) ($this->confirmParams['id'] ?? 0);
                if (Schema::hasTable('support_maintenance_usages')) {
                    DB::transaction(function () use ($id) {
                        $u = DB::table('support_maintenance_usages')->where('id', $id)->lockForUpdate()->first();
                        if ($u) {
                            if ($u->material_id && Schema::hasTable('support_maintenance_materials')) {
                                DB::table('support_maintenance_materials')
                                    ->where('id', $u->material_id)
                                    ->increment('stock', (int) $u->quantity, ['updated_at' => now()]);
                            }
                            DB::table('support_maintenance_usages')->where('id', $id)->delete();
                        }
                    });
                    session()->flash('success', 'Catatan pemakaian dibatalkan & stok telah dikembalikan.');
                    $this->loadMaterial();
                }
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal membatalkan pemakaian: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }

        parent::handleConfirm();
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            if ($tab === 'calendar') {
                $this->loadCalendar();
            }
            if ($tab === 'technician') {
                $this->loadTechnicianStats();
            }
            if ($tab === 'material') {
                $this->loadMaterial();
            }
        }
    }

    public function updatedSearch(string $value = ''): void
    {
        parent::updatedSearch($value);
        if ($this->activeTab === 'material') {
            $this->loadMaterial();
        }
    }

    public function getToolbarActions(): array
    {
        return [
            ['label' => 'Jadwal Baru', 'icon' => 'play', 'action' => 'openCreate()'],
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Pemakaian Material', 'icon' => 'package', 'action' => 'openUsageModal()'],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'complete', 'label' => 'Tandai Selesai', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['key' => 'export', 'label' => 'Export'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'type', 'label' => 'Jenis Pemeliharaan', 'type' => 'select', 'options' => $opts['types'] ?? []],
            ['key' => 'technician_id', 'label' => 'Teknisi', 'type' => 'select', 'options' => $opts['technicians'] ?? []],
            ['key' => 'device_type', 'label' => 'Tipe Perangkat', 'type' => 'select', 'options' => $opts['device_types'] ?? []],
            ['key' => 'scheduled_from', 'label' => 'Jadwal (Mulai)', 'type' => 'date'],
            ['key' => 'scheduled_to', 'label' => 'Jadwal (Sampai)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Total Jadwal', 'value' => $s['total'] ?? 0, 'color' => 'slate', 'icon' => 'wrench'],
            ['label' => 'Dijadwalkan', 'value' => $s['scheduled'] ?? 0, 'color' => 'blue', 'icon' => 'calendar'],
            ['label' => 'Dikerjakan', 'value' => $s['in_progress'] ?? 0, 'color' => 'amber', 'icon' => 'activity'],
            ['label' => 'Selesai', 'value' => $s['completed'] ?? 0, 'color' => 'emerald', 'icon' => 'check-circle'],
            ['label' => 'Preventive', 'value' => $s['preventive'] ?? 0, 'color' => 'sky', 'icon' => 'shield'],
            ['label' => 'Terlambat', 'value' => $s['overdue'] ?? 0, 'color' => 'red', 'icon' => 'alert-triangle'],
        ];
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.support.maintenance.index', [
            'rows' => $rows,
            'calendar' => $this->calendar,
            'technicianLoad' => $this->technicianLoad,
            'stock' => $this->stock,
            'usage' => $this->usage,
            'deviceOptions' => $this->deviceOptions,
        ]);
    }
}
