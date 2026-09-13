<?php

namespace App\Livewire\Support\Maintenance;

use App\Livewire\BaseEnterpriseList;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'support';
    public string $activePage = 'maintenance';
    public string $sortField = 'scheduled_date';

    public array $tabs = [
        'calendar' => 'Calendar',
        'history' => 'History',
        'technician' => 'Technician',
        'material' => 'Material',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $calendarEvents = null;
    public ?array $technicianStats = null;
    public ?array $materialStock = null;
    public ?array $materialUsage = null;
    public bool $showCreateModal = false;
    public array $newMaint = [];
    public bool $showUsageModal = false;
    public array $usageForm = [];
    public string $calendarMonth = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'calendar';
        $this->calendarMonth = now()->format('Y-m');
        $this->filters = [
            'status' => '',
            'type' => '',
            'technician_id' => '',
            'device_type' => '',
            'scheduled_from' => now()->startOfMonth()->toDateString(),
            'scheduled_to' => now()->endOfMonth()->toDateString(),
        ];
        $this->newMaint = [
            'type' => 'preventive',
            'title' => '',
            'device_type' => 'router',
            'device_id' => '',
            'scheduled_date' => now()->addDays(7)->toDateString(),
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
        $this->usageForm = [
            'maintenance_id' => '',
            'material_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'notes' => '',
        ];
        $this->loadAll();
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    protected function tableName(): string
    {
        try {
            $exists = DB::table('information_schema.tables')
                ->where('table_schema', env('DB_DATABASE'))
                ->where('table_name', 'support_maintenances')
                ->exists();
            return $exists ? 'support_maintenances' : 'tickets';
        } catch (Throwable) {
            return 'tickets';
        }
    }

    public function loadAll(): void
    {
        try {
            $tbl = $this->tableName();
            $q = DB::table($tbl);
            $isTicket = $tbl === 'tickets';
            $statusCol = $isTicket ? 'status' : 'status';
            $this->summary = [
                'total' => $q->count(),
                'scheduled' => (clone $q)->where($statusCol, 'scheduled')->count(),
                'in_progress' => (clone $q)->where($statusCol, 'in_progress')->count(),
                'completed' => (clone $q)->where($statusCol, 'completed')->count(),
                'preventive' => (clone $q)->where('category', 'installation')->count() + (clone $q)->where('priority', 'low')->count(),
                'corrective' => (clone $q)->whereIn('status', ['open', 'in_progress'])->count(),
                'overdue' => (clone $q)->whereDate($isTicket ? 'due_date' : 'created_at', '<', now()->toDateString())
                    ->whereIn($statusCol, ['scheduled', 'pending', 'open'])->count(),
            ];
        } catch (Throwable $e) {
            Log::error('Maintenance summary failed', ['e' => $e->getMessage()]);
            $this->summary = [
                'total' => 0, 'scheduled' => 0, 'in_progress' => 0, 'completed' => 0,
                'preventive' => 0, 'corrective' => 0, 'overdue' => 0,
            ];
        }

        try {
            $techs = User::whereHas('roles', fn($q) => $q->whereIn('name', ['administrator', 'manager']))
                ->pluck('name', 'id')->all();
        } catch (Throwable) {
            $techs = User::limit(100)->pluck('name', 'id')->all();
        }

        $this->filterOptions = [
            'statuses' => [
                'scheduled' => 'Scheduled',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'open' => 'Open',
            ],
            'types' => [
                'preventive' => 'Preventive (Pencegahan)',
                'corrective' => 'Corrective (Perbaikan)',
                'predictive' => 'Predictive',
                'emergency' => 'Emergency',
            ],
            'technicians' => $techs,
            'device_types' => [
                'router' => 'Router / NAS',
                'olt' => 'OLT',
                'onu' => 'ONU / ONT',
                'odp' => 'ODP',
                'odc' => 'ODC',
                'pop' => 'POP',
                'tower' => 'Tower',
                'server' => 'Server',
                'other' => 'Lainnya',
            ],
            'priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
        ];

        $this->loadCalendar();
        $this->loadTechnicianStats();
        $this->loadMaterial();
    }

    protected function loadCalendar(): void
    {
        try {
            $tbl = $this->tableName();
            $month = $this->calendarMonth ?: now()->format('Y-m');
            $start = date('Y-m-01', strtotime($month . '-01'));
            $end = date('Y-m-t', strtotime($month . '-01'));
            $isTicket = $tbl === 'tickets';
            $dateCol = $isTicket ? 'created_at' : 'created_at';
            $rows = DB::table($tbl)
                ->whereBetween(DB::raw("DATE({$dateCol})"), [$start, $end])
                ->limit(500)
                ->get(['id', 'title', 'status', 'priority', 'assigned_to', 'created_at']);
            $events = [];
            foreach ($rows as $r) {
                $events[] = [
                    'id' => $r->id,
                    'title' => $r->title ?? ('Maintenance #' . $r->id),
                    'date' => substr((string)$r->created_at, 0, 10),
                    'status' => $r->status,
                    'priority' => $r->priority,
                    'technician' => $r->assigned_to ?? null,
                ];
            }
            $this->calendarEvents = $events;
        } catch (Throwable $e) {
            Log::error('Maintenance calendar failed', ['e' => $e->getMessage()]);
            $this->calendarEvents = [];
        }
    }

    protected function loadTechnicianStats(): void
    {
        try {
            $techs = User::whereHas('roles', fn($q) => $q->whereIn('name', ['administrator', 'manager']))
                ->get(['id', 'name']);
            $monthFrom = now()->startOfMonth()->toDateString();
            $monthTo = now()->endOfMonth()->toDateString();
            $tbl = $this->tableName();
            $isTicket = $tbl === 'tickets';
            $dateCol = $isTicket ? 'created_at' : 'created_at';
            $techCol = $isTicket ? 'assigned_to' : 'assigned_to';
            $stats = [];
            foreach ($techs as $t) {
                $q = DB::table($tbl)->where($techCol, $t->id)
                    ->whereBetween(DB::raw("DATE({$dateCol})"), [$monthFrom, $monthTo]);
                $done = (clone $q)->where('status', 'completed')->count();
                $all = (clone $q)->count();
                $stats[] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'total' => $all,
                    'completed' => $done,
                    'pending' => max(0, $all - $done),
                    'rate' => $all > 0 ? round(($done / $all) * 100, 1) : 0,
                ];
            }
            $this->technicianStats = $stats;
        } catch (Throwable $e) {
            Log::error('Maintenance technician stats failed', ['e' => $e->getMessage()]);
            $this->technicianStats = [];
        }
    }

    protected function loadMaterial(): void
    {
        $this->materialStock = [
            ['id' => 1, 'name' => 'Patch Cord SC/UPC 3m', 'sku' => 'PC-SC-3M', 'stock' => 150, 'unit' => 'pcs', 'min' => 20],
            ['id' => 2, 'name' => 'ONU GPON 1GE', 'sku' => 'ONU-GPON-1GE', 'stock' => 45, 'unit' => 'pcs', 'min' => 10],
            ['id' => 3, 'name' => 'Drop Core 1 Core 50m', 'sku' => 'DC-1C-50M', 'stock' => 28, 'unit' => 'roll', 'min' => 5],
            ['id' => 4, 'name' => 'Connector SC/UPC', 'sku' => 'CON-SC-UPC', 'stock' => 320, 'unit' => 'pcs', 'min' => 50],
            ['id' => 5, 'name' => 'ODP 8 Port', 'sku' => 'ODP-8P', 'stock' => 12, 'unit' => 'pcs', 'min' => 3],
            ['id' => 6, 'name' => 'Mikrotik Router hAP', 'sku' => 'MK-hAP', 'stock' => 8, 'unit' => 'pcs', 'min' => 2],
        ];

        $this->materialUsage = [
            ['date' => now()->subDays(5)->toDateString(), 'wo' => 'WO-1021', 'material' => 'ONU GPON 1GE', 'qty' => 1, 'tech' => 'Budi Santoso'],
            ['date' => now()->subDays(5)->toDateString(), 'wo' => 'WO-1021', 'material' => 'Patch Cord SC/UPC 3m', 'qty' => 2, 'tech' => 'Budi Santoso'],
            ['date' => now()->subDays(4)->toDateString(), 'wo' => 'MT-502', 'material' => 'Connector SC/UPC', 'qty' => 8, 'tech' => 'Ahmad Fauzi'],
            ['date' => now()->subDays(3)->toDateString(), 'wo' => 'WO-1022', 'material' => 'Drop Core 1 Core 50m', 'qty' => 1, 'tech' => 'Dedi Wijaya'],
            ['date' => now()->subDays(2)->toDateString(), 'wo' => 'MT-503', 'material' => 'ODP 8 Port', 'qty' => 1, 'tech' => 'Budi Santoso'],
            ['date' => now()->subDay()->toDateString(), 'wo' => 'WO-1025', 'material' => 'Mikrotik Router hAP', 'qty' => 1, 'tech' => 'Ahmad Fauzi'],
        ];
    }

    public function updatedCalendarMonth(): void
    {
        if ($this->activeTab === 'calendar') $this->loadCalendar();
    }

    public function getRowsQuery()
    {
        return $this->buildQuery()->limit(min(500, $this->perPage * 10));
    }

    protected function buildQuery()
    {
        $tbl = $this->tableName();
        $q = DB::table($tbl);
        $isTicket = $tbl === 'tickets';
        $statusCol = 'status';
        $dateCol = $isTicket ? 'created_at' : 'created_at';
        $techCol = 'assigned_to';

        if (!empty($this->filters['status'])) {
            $q->where($statusCol, $this->filters['status']);
        }
        if (!empty($this->filters['type']) && !$isTicket) {
            $q->where('category', $this->filters['type']);
        }
        if (!empty($this->filters['technician_id'])) {
            $q->where($techCol, $this->filters['technician_id']);
        }
        if (!empty($this->filters['scheduled_from'])) {
            $q->whereDate($dateCol, '>=', $this->filters['scheduled_from']);
        }
        if (!empty($this->filters['scheduled_to'])) {
            $q->whereDate($dateCol, '<=', $this->filters['scheduled_to']);
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $q->where(function ($qq) use ($s, $tbl) {
                $qq->where('title', 'like', $s)
                    ->orWhere('description', 'like', $s)
                    ->orWhere('id', 'like', $s);
            });
        }

        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $field = in_array($this->sortField, ['id', 'status', 'priority', 'created_at', 'due_date'], true)
            ? $this->sortField : 'created_at';
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
                    $tbl = $this->tableName();
                    $n = DB::table($tbl)->whereIn('id', $ids)->update(['status' => 'completed', 'updated_at' => now()]);
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
        $tbl = $this->tableName();
        $rows = DB::table($tbl)->whereIn('id', $ids)->get()->all();
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
        $headers = ['ID', 'Title', 'Status', 'Priority', 'Created At'];
        return response()->stream(function () use ($rows, $headers) {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, $headers);
            foreach ($rows as $r) {
                $data = (array)$r;
                fputcsv($fh, [
                    $data['id'] ?? '',
                    $data['title'] ?? '',
                    $data['status'] ?? '',
                    $data['priority'] ?? '',
                    substr((string)($data['created_at'] ?? ''), 0, 19),
                ]);
            }
            fclose($fh);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function openCreate(): void
    {
        $this->newMaint = [
            'type' => 'preventive',
            'title' => '',
            'device_type' => 'router',
            'device_id' => '',
            'scheduled_date' => now()->addDays(7)->toDateString(),
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
    }

    public function submitCreate(): void
    {
        if (empty($this->newMaint['title'])) {
            $this->errorMessage = 'Title wajib diisi.';
            return;
        }
        try {
            $tbl = $this->tableName();
            $uid = Auth::id() ?? 0;
            $id = DB::table($tbl)->insertGetId([
                'title' => $this->newMaint['title'],
                'description' => $this->newMaint['notes'] ?? '',
                'status' => 'scheduled',
                'priority' => $this->newMaint['priority'] ?? 'medium',
                'category' => $this->newMaint['type'] ?? 'preventive',
                'assigned_to' => $this->newMaint['technician_id'] ?: null,
                'created_by' => $uid,
                'customer_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            session()->flash('success', 'Jadwal Maintenance #' . $id . ' dibuat.');
            $this->showCreateModal = false;
            $this->loadAll();
            $this->resetPage();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal create: ' . $e->getMessage();
        }
    }

    public function openUsageModal(): void
    {
        $this->usageForm = [
            'maintenance_id' => '',
            'material_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'notes' => '',
        ];
        $this->showUsageModal = true;
    }

    public function closeUsageModal(): void
    {
        $this->showUsageModal = false;
    }

    public function submitUsage(): void
    {
        if (empty($this->usageForm['material_name']) || (int)($this->usageForm['quantity'] ?? 0) <= 0) {
            $this->errorMessage = 'Material & Quantity wajib diisi.';
            return;
        }
        session()->flash('success', 'Pemakaian material dicatat (demo).');
        $this->closeUsageModal();
    }

    public function rowStart(int $id): void
    {
        try {
            DB::table($this->tableName())->where('id', $id)->update([
                'status' => 'in_progress', 'updated_at' => now(),
            ]);
            session()->flash('success', 'Maintenance #' . $id . ' dimulai.');
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal start: ' . $e->getMessage();
        }
    }

    public function rowComplete(int $id): void
    {
        $this->confirmTitle = 'Selesaikan Maintenance #' . $id;
        $this->confirmMessage = 'Pastikan pekerjaan dan laporan teknisi sudah lengkap. Selesaikan maintenance ini?';
        $this->confirmAction = 'complete-maint';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Selesaikan';
        $this->confirmBtnClass = 'bg-emerald-600 hover:bg-emerald-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function rowDetail(int $id): void
    {
        try {
            $row = DB::table($this->tableName())->where('id', $id)->first();
            if (!$row) return;
            $tech = $row->assigned_to ? (User::find($row->assigned_to)?->name ?? '-') : '-';
            $this->confirmTitle = 'Maintenance #' . $id;
            $this->confirmMessage = ($row->title ?? '-') . ' | Status: ' . strtoupper((string)($row->status ?? '')) .
                ' | Teknisi: ' . $tech . ' | Priority: ' . ($row->priority ?? '');
            $this->confirmAction = '';
            $this->confirmBtnText = 'Tutup';
            $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
            $this->dispatch('open-modal', name: $this->confirmModal);
        } catch (Throwable) {
        }
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'complete-maint') {
            try {
                $id = (int)($this->confirmParams['id'] ?? 0);
                DB::table($this->tableName())->where('id', $id)->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
                session()->flash('success', 'Maintenance #' . $id . ' selesai.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal complete: ' . $e->getMessage();
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
            if ($tab === 'calendar') $this->loadCalendar();
            if ($tab === 'technician') $this->loadTechnicianStats();
            if ($tab === 'material') $this->loadMaterial();
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
            ['key' => 'complete', 'label' => 'Complete', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['key' => 'export', 'label' => 'Export'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'type', 'label' => 'Jenis', 'type' => 'select', 'options' => $opts['types'] ?? []],
            ['key' => 'technician_id', 'label' => 'Teknisi', 'type' => 'select', 'options' => $opts['technicians'] ?? []],
            ['key' => 'device_type', 'label' => 'Perangkat', 'type' => 'select', 'options' => $opts['device_types'] ?? []],
            ['key' => 'scheduled_from', 'label' => 'Tgl (Awal)', 'type' => 'date'],
            ['key' => 'scheduled_to', 'label' => 'Tgl (Akhir)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Total', 'value' => $s['total'] ?? 0, 'color' => 'blue', 'icon' => 'file-text'],
            ['label' => 'Scheduled', 'value' => $s['scheduled'] ?? 0, 'color' => 'amber', 'icon' => 'calendar'],
            ['label' => 'In Progress', 'value' => $s['in_progress'] ?? 0, 'color' => 'purple', 'icon' => 'activity'],
            ['label' => 'Completed', 'value' => $s['completed'] ?? 0, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Preventive', 'value' => $s['preventive'] ?? 0, 'color' => 'cyan', 'icon' => 'shield'],
            ['label' => 'Overdue', 'value' => $s['overdue'] ?? 0, 'color' => 'red', 'icon' => 'alert-triangle'],
        ];
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.support.maintenance.index', compact('rows'));
    }
}
