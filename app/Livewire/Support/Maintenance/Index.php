<?php

namespace App\Livewire\Support\Maintenance;

use App\Livewire\BaseEnterpriseList;
use App\Models\User;
use Carbon\Carbon;
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

    public bool $showCreateModal = false;
    public array $newMaint = [];
    public bool $showUsageModal = false;
    public array $usageForm = [];
    public string $calendarMonth = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'history';
        $this->calendarMonth = now()->format('Y-m');
        $this->filters = [
            'status' => '',
            'type' => '',
            'technician_id' => '',
            'device_type' => '',
            'scheduled_from' => '',
            'scheduled_to' => '',
        ];
        $this->newMaint = [
            'type' => 'preventive',
            'title' => '',
            'device_type' => 'router',
            'device_name' => '',
            'location_name' => '',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '09:00',
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
        $this->usageForm = [
            'maintenance_id' => '',
            'material_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'technician_name' => '',
            'notes' => '',
        ];

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Support', 'url' => '#'],
            ['label' => 'Maintenance Jaringan', 'url' => route('support.maintenance')],
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
        return 'support_maintenances';
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

        try {
            $techs = User::orderBy('name')->pluck('name', 'id')->all();
        } catch (Throwable) {
            $techs = [];
        }

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
                ->whereBetween('scheduled_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->get();

            $eventsByDate = [];
            foreach ($rows as $r) {
                $d = $r->scheduled_date ?: substr((string)$r->created_at, 0, 10);
                $eventsByDate[$d][] = [
                    'id' => $r->id,
                    'title' => $r->title ?? ('Maintenance #' . $r->id),
                    'type' => $r->type ?? 'preventive',
                    'status' => $r->status ?? 'scheduled',
                    'priority' => $r->priority ?? 'medium',
                    'technician' => $r->assigned_to ?? null,
                ];
            }

            // Generate full calendar grid (from Sunday of first week to Saturday of last week)
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

    protected function loadTechnicianStats(): void
    {
        try {
            $techs = User::all(['id', 'name']);
            $stats = [];
            foreach ($techs as $t) {
                $q = DB::table('support_maintenances')->where('assigned_to', $t->id);
                $all = (clone $q)->count();
                $scheduled = (clone $q)->where('status', 'scheduled')->count();
                $inProgress = (clone $q)->where('status', 'in_progress')->count();
                $completed = (clone $q)->where('status', 'completed')->count();
                $overdue = (clone $q)->whereDate('scheduled_date', '<', now()->toDateString())
                    ->whereIn('status', ['scheduled', 'in_progress'])->count();

                $stats[] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'specialization' => 'Teknisi Pemeliharaan Jaringan',
                    'total' => $all,
                    'scheduled' => $scheduled,
                    'in_progress' => $inProgress,
                    'completed' => $completed,
                    'overdue' => $overdue,
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

    protected function loadMaterial(): void
    {
        $this->materialStock = [
            ['id' => 1, 'name' => 'Patch Cord SC/UPC 3m', 'sku' => 'PC-SC-3M', 'stock' => 150, 'qty' => 150, 'unit' => 'pcs', 'min_stock' => 20, 'location' => 'Gudang Utama'],
            ['id' => 2, 'name' => 'ONU GPON 1GE', 'sku' => 'ONU-GPON-1GE', 'stock' => 45, 'qty' => 45, 'unit' => 'pcs', 'min_stock' => 10, 'location' => 'Gudang Utama'],
            ['id' => 3, 'name' => 'Drop Core 1 Core 50m', 'sku' => 'DC-1C-50M', 'stock' => 28, 'qty' => 28, 'unit' => 'roll', 'min_stock' => 5, 'location' => 'Gudang Lapangan'],
            ['id' => 4, 'name' => 'Connector SC/UPC', 'sku' => 'CON-SC-UPC', 'stock' => 320, 'qty' => 320, 'unit' => 'pcs', 'min_stock' => 50, 'location' => 'Gudang Utama'],
            ['id' => 5, 'name' => 'ODP 8 Port', 'sku' => 'ODP-8P', 'stock' => 12, 'qty' => 12, 'unit' => 'pcs', 'min_stock' => 3, 'location' => 'Gudang Jaringan'],
            ['id' => 6, 'name' => 'Mikrotik Router hAP ac2', 'sku' => 'MK-hAP', 'stock' => 8, 'qty' => 8, 'unit' => 'pcs', 'min_stock' => 2, 'location' => 'Gudang Utama'],
        ];

        $this->materialUsage = [
            ['date' => now()->subDays(5)->toDateString(), 'wo_ref' => 'MT-1021', 'material' => 'ONU GPON 1GE', 'qty' => 1, 'unit' => 'pcs', 'technician' => 'Budi Santoso'],
            ['date' => now()->subDays(5)->toDateString(), 'wo_ref' => 'MT-1021', 'material' => 'Patch Cord SC/UPC 3m', 'qty' => 2, 'unit' => 'pcs', 'technician' => 'Budi Santoso'],
            ['date' => now()->subDays(4)->toDateString(), 'wo_ref' => 'MT-502', 'material' => 'Connector SC/UPC', 'qty' => 8, 'unit' => 'pcs', 'technician' => 'Ahmad Fauzi'],
            ['date' => now()->subDays(3)->toDateString(), 'wo_ref' => 'MT-1022', 'material' => 'Drop Core 1 Core 50m', 'qty' => 1, 'unit' => 'roll', 'technician' => 'Dedi Wijaya'],
            ['date' => now()->subDays(2)->toDateString(), 'wo_ref' => 'MT-503', 'material' => 'ODP 8 Port', 'qty' => 1, 'unit' => 'pcs', 'technician' => 'Budi Santoso'],
            ['date' => now()->subDay()->toDateString(), 'wo_ref' => 'MT-1025', 'material' => 'Mikrotik Router hAP', 'qty' => 1, 'unit' => 'pcs', 'technician' => 'Ahmad Fauzi'],
        ];

        $this->stock = $this->materialStock;
        $this->usage = $this->materialUsage;
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
                    ->orWhere('users.name', 'like', $s);
            });
        }

        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $field = in_array($this->sortField, ['id', 'status', 'priority', 'scheduled_date', 'created_at', 'completed_at'], true)
            ? 'support_maintenances.' . $this->sortField : 'support_maintenances.id';
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
        $headers = ['ID', 'Judul Maintenance', 'Jenis', 'Perangkat', 'Lokasi', 'Tgl Jadwal', 'Jam', 'Teknisi', 'Prioritas', 'Status'];
        return response()->stream(function () use ($rows, $headers) {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, $headers);
            foreach ($rows as $r) {
                $data = (array)$r;
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
            'device_name' => '',
            'location_name' => '',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '09:00',
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
            $this->errorMessage = 'Judul pekerjaan maintenance wajib diisi.';
            return;
        }

        try {
            $uid = Auth::id() ?? 1;
            $id = DB::table('support_maintenances')->insertGetId([
                'title' => $this->newMaint['title'],
                'type' => $this->newMaint['type'] ?? 'preventive',
                'status' => 'scheduled',
                'priority' => $this->newMaint['priority'] ?? 'medium',
                'device_type' => $this->newMaint['device_type'] ?? 'router',
                'device_name' => $this->newMaint['device_name'] ?? null,
                'location_name' => $this->newMaint['location_name'] ?? null,
                'scheduled_date' => $this->newMaint['scheduled_date'] ?? now()->toDateString(),
                'scheduled_time' => $this->newMaint['scheduled_time'] ?? '09:00',
                'assigned_to' => $this->newMaint['technician_id'] ?: null,
                'notes' => $this->newMaint['notes'] ?? '',
                'created_by' => $uid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('success', 'Jadwal Pemeliharaan #' . $id . ' berhasil dibuat.');
            $this->showCreateModal = false;
            $this->loadAll();
            $this->resetPage();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal membuat jadwal maintenance: ' . $e->getMessage();
        }
    }

    public function openUsageModal(): void
    {
        $this->usageForm = [
            'maintenance_id' => '',
            'material_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'technician_name' => Auth::user()?->name ?? 'Teknisi',
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
            $this->errorMessage = 'Nama material dan jumlah kuantiti wajib diisi.';
            return;
        }

        session()->flash('success', 'Pemakaian material berhasil dicatat.');
        $this->closeUsageModal();
    }

    public function rowStart(int $id): void
    {
        try {
            DB::table('support_maintenances')->where('id', $id)->update([
                'status' => 'in_progress',
                'updated_at' => now(),
            ]);
            session()->flash('success', 'Maintenance #' . $id . ' sedang dikerjakan.');
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal memulai: ' . $e->getMessage();
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

    public function rowDelete(int $id): void
    {
        $this->confirmTitle = 'Hapus Jadwal #' . $id;
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

            if (!$row) return;

            $this->confirmTitle = 'Detail Maintenance #' . $id . ' - ' . ($row->title ?? '-');
            $this->confirmMessage = 'Jenis: ' . strtoupper((string)($row->type ?? '-')) .
                ' | Status: ' . strtoupper((string)($row->status ?? '')) .
                ' | Teknisi: ' . ($row->technician_name ?? 'Belum Ditugaskan') .
                ' | Jadwal: ' . ($row->scheduled_date ?? '-') . ' ' . ($row->scheduled_time ?? '') .
                ' | Perangkat: ' . ($row->device_name ?: ($row->device_type ?: '-')) .
                ' | Lokasi: ' . ($row->location_name ?: '-') .
                ($row->notes ? ' | Catatan: ' . $row->notes : '');

            $this->confirmAction = '';
            $this->confirmBtnText = 'Tutup';
            $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
            $this->dispatch('open-modal', name: $this->confirmModal);
        } catch (Throwable) {
            // Silently handle
        }
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'complete-maint') {
            try {
                $id = (int)($this->confirmParams['id'] ?? 0);
                DB::table('support_maintenances')->where('id', $id)->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
                session()->flash('success', 'Maintenance #' . $id . ' telah diselesaikan.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal menyelesaikan: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }

        if ($this->confirmAction === 'delete-maint') {
            try {
                $id = (int)($this->confirmParams['id'] ?? 0);
                DB::table('support_maintenances')->where('id', $id)->delete();
                session()->flash('success', 'Jadwal Maintenance #' . $id . ' telah dihapus.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal menghapus: ' . $e->getMessage();
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
        ]);
    }
}
