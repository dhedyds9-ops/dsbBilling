<?php

namespace App\Livewire\Support\Installation;

use App\Livewire\BaseEnterpriseList;
use App\Models\Crm\Installation as CrmInstallation;
use App\Models\Crm\Customer;
use App\Models\ISP\Pop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'support';
    public string $activePage = 'installation';
    public string $sortField = 'scheduled_date';

    public array $tabs = [
        'workorder' => 'Work Order',
        'schedule' => 'Schedule',
        'technician' => 'Technician',
        'checklist' => 'Checklist',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $calendarEvents = null;
    public ?array $technicianLoad = null;
    public ?array $checklistTemplate = null;
    public bool $showAssignModal = false;
    public ?int $assignTechId = null;
    public ?int $assignWoId = null;
    public bool $showCreateWo = false;
    public array $newWo = [];
    public string $scheduleDateFrom = '';
    public string $scheduleDateTo = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'workorder';
        $this->filters = [
            'status' => '',
            'technician_id' => '',
            'customer_id' => '',
            'pop_id' => '',
            'priority' => '',
            'scheduled_from' => now()->startOfWeek()->toDateString(),
            'scheduled_to' => now()->endOfWeek()->toDateString(),
        ];
        $this->newWo = [
            'customer_id' => '',
            'package_id' => '',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
        $this->scheduleDateFrom = now()->startOfMonth()->toDateString();
        $this->scheduleDateTo = now()->endOfMonth()->toDateString();
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

    public function loadAll(): void
    {
        try {
            $woQuery = CrmInstallation::query();
            $this->summary = [
                'total_wo' => $woQuery->count(),
                'pending' => (clone $woQuery)->where('status', 'pending')->count(),
                'scheduled' => (clone $woQuery)->where('status', 'scheduled')->count(),
                'in_progress' => (clone $woQuery)->where('status', 'in_progress')->count(),
                'completed' => (clone $woQuery)->where('status', 'completed')->count(),
                'cancelled' => (clone $woQuery)->where('status', 'cancelled')->count(),
                'today_scheduled' => (clone $woQuery)->whereDate('scheduled_date', now()->toDateString())->count(),
            ];
        } catch (Throwable $e) {
            Log::error('Installation summary failed', ['e' => $e->getMessage()]);
            $this->summary = [
                'total_wo' => 0, 'pending' => 0, 'scheduled' => 0, 'in_progress' => 0,
                'completed' => 0, 'cancelled' => 0, 'today_scheduled' => 0,
            ];
        }

        $statuses = [
            'pending' => 'Pending',
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        try {
            $techs = User::whereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'admin', 'super_admin']))
                ->pluck('name', 'id')->all();
        } catch (Throwable) {
            $techs = User::pluck('name', 'id')->all();
        }

        try {
            $customers = Customer::limit(500)->pluck('name', 'id')->all();
        } catch (Throwable) {
            $customers = [];
        }

        try {
            $pops = Pop::pluck('name', 'id')->all();
        } catch (Throwable) {
            $pops = [];
        }

        $this->filterOptions = [
            'statuses' => $statuses,
            'technicians' => $techs,
            'customers' => $customers,
            'pops' => $pops,
            'priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
        ];

        $this->loadCalendar();
        $this->loadTechnicianLoad();
        $this->checklistTemplate = [
            ['id' => 1, 'item' => 'Survey lokasi & titik kabel', 'required' => true],
            ['id' => 2, 'item' => 'Pemasangan kabel drop core', 'required' => true],
            ['id' => 3, 'item' => 'Pemasangan ONT / ONU', 'required' => true],
            ['id' => 4, 'item' => 'Setting PPPoE / Hotspot', 'required' => true],
            ['id' => 5, 'item' => 'Test koneksi & speedtest', 'required' => true],
            ['id' => 6, 'item' => 'Penandatanganan berita acara', 'required' => true],
            ['id' => 7, 'item' => 'Foto dokumentasi lokasi', 'required' => false],
        ];
    }

    protected function loadCalendar(): void
    {
        try {
            $from = $this->scheduleDateFrom ?: now()->startOfMonth()->toDateString();
            $to = $this->scheduleDateTo ?: now()->endOfMonth()->toDateString();
            $rows = CrmInstallation::query()
                ->whereBetween('scheduled_date', [$from, $to])
                ->limit(500)
                ->get(['id', 'customer_id', 'scheduled_date', 'scheduled_time', 'status', 'technician_id']);
            $events = [];
            foreach ($rows as $r) {
                $events[] = [
                    'id' => $r->id,
                    'title' => 'WO #' . $r->id,
                    'date' => $r->scheduled_date,
                    'time' => $r->scheduled_time ?: '-',
                    'status' => $r->status,
                    'technician' => $r->technician_id,
                    'customer' => $r->customer_id,
                ];
            }
            $this->calendarEvents = $events;
        } catch (Throwable $e) {
            Log::error('Installation calendar failed', ['e' => $e->getMessage()]);
            $this->calendarEvents = [];
        }
    }

    protected function loadTechnicianLoad(): void
    {
        try {
            $techs = User::whereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'admin']))
                ->get(['id', 'name']);
            $load = [];
            $weekFrom = now()->startOfWeek()->toDateString();
            $weekTo = now()->endOfWeek()->toDateString();
            foreach ($techs as $t) {
                $pending = CrmInstallation::where('technician_id', $t->id)
                    ->whereBetween('scheduled_date', [$weekFrom, $weekTo])
                    ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                    ->count();
                $completed = CrmInstallation::where('technician_id', $t->id)
                    ->whereBetween('scheduled_date', [$weekFrom, $weekTo])
                    ->where('status', 'completed')
                    ->count();
                $load[] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'pending' => $pending,
                    'completed' => $completed,
                    'total' => $pending + $completed,
                ];
            }
            $this->technicianLoad = $load;
        } catch (Throwable $e) {
            Log::error('Installation technician load failed', ['e' => $e->getMessage()]);
            $this->technicianLoad = [];
        }
    }

    public function getRowsQuery()
    {
        return $this->buildWoQuery()->limit(min(500, $this->perPage * 10));
    }

    protected function buildWoQuery()
    {
        $q = CrmInstallation::query();

        if (!empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['technician_id'])) {
            $q->where('technician_id', $this->filters['technician_id']);
        }
        if (!empty($this->filters['customer_id'])) {
            $q->where('customer_id', $this->filters['customer_id']);
        }
        if (!empty($this->filters['pop_id'])) {
            $q->where('pop_id', $this->filters['pop_id']);
        }
        if (!empty($this->filters['priority'])) {
            $q->where('priority', $this->filters['priority']);
        }
        if (!empty($this->filters['scheduled_from'])) {
            $q->whereDate('scheduled_date', '>=', $this->filters['scheduled_from']);
        }
        if (!empty($this->filters['scheduled_to'])) {
            $q->whereDate('scheduled_date', '<=', $this->filters['scheduled_to']);
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $q->where(function ($qq) use ($s) {
                $qq->where('notes', 'like', $s)
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', $s)->orWhere('code', 'like', $s));
            });
        }

        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $field = in_array($this->sortField, ['id', 'scheduled_date', 'scheduled_time', 'status', 'created_at'], true)
            ? $this->sortField : 'scheduled_date';
        $q->orderBy($field, $dir);

        return $q;
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->buildWoQuery()->paginate($this->perPage);
        }, 'Gagal memuat data Instalasi');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 0;
        try {
            return match ($action) {
                'assign' => (function () use ($ids, $userId) {
                    if (!$this->assignTechId) return 0;
                    $n = 0;
                    foreach ($ids as $id) {
                        $wo = CrmInstallation::find((int)$id);
                        if ($wo) {
                            $wo->technician_id = $this->assignTechId;
                            if ($wo->status === 'pending') $wo->status = 'scheduled';
                            $wo->save();
                            $n++;
                        }
                    }
                    $this->loadAll();
                    return $n;
                })(),
                'complete' => (function () use ($ids) {
                    $n = CrmInstallation::whereIn('id', $ids)->update(['status' => 'completed']);
                    $this->loadAll();
                    return $n;
                })(),
                'cancel' => (function () use ($ids) {
                    $n = CrmInstallation::whereIn('id', $ids)->update(['status' => 'cancelled']);
                    $this->loadAll();
                    return $n;
                })(),
                'export' => $this->doBulkExport($ids),
                default => 0,
            };
        } catch (Throwable $e) {
            Log::error('Installation bulk action failed', ['e' => $e->getMessage()]);
            return 0;
        }
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = CrmInstallation::whereIn('id', $ids)->get()->all();
        $this->exportCsvDirect($rows);
        return count($ids);
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $rows = $this->buildWoQuery()->limit(5000)->get()->all();
            return $this->buildCsvResponse($rows, 'instalasi_' . now()->format('Ymd') . '.csv');
        } catch (Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return back();
        }
    }

    public function exportCsvDirect(array $rows): void
    {
        $resp = $this->buildCsvResponse($rows, 'instalasi_wo_' . now()->format('Ymd_His') . '.csv');
        $resp->send();
        exit;
    }

    protected function buildCsvResponse(array $rows, string $filename): StreamedResponse
    {
        $headers = ['ID', 'Customer', 'Tgl Jadwal', 'Waktu', 'Teknisi', 'Status', 'Priority', 'Notes'];
        return response()->stream(function () use ($rows, $headers) {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, $headers);
            foreach ($rows as $r) {
                $data = $r instanceof \Illuminate\Database\Eloquent\Model ? $r->toArray() : (array)$r;
                fputcsv($fh, [
                    $data['id'] ?? '',
                    $data['customer_name'] ?? ($r->customer->name ?? ''),
                    $data['scheduled_date'] ?? '',
                    $data['scheduled_time'] ?? '',
                    $r->technician->name ?? ($data['technician_name'] ?? ''),
                    $data['status'] ?? '',
                    $data['priority'] ?? '',
                    strip_tags($data['notes'] ?? ''),
                ]);
            }
            fclose($fh);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function applyBulk(string $action): void
    {
        if ($action === 'assign') {
            if (count($this->selected) === 0) {
                $this->errorMessage = 'Pilih Work Order terlebih dahulu.';
                return;
            }
            $this->assignTechId = null;
            $this->assignWoId = null;
            $this->showAssignModal = true;
            return;
        }
        parent::applyBulk($action);
    }

    public function submitBulkAssign(): void
    {
        if (!$this->assignTechId) {
            $this->errorMessage = 'Pilih teknisi.';
            return;
        }
        $this->executeBulk('assign');
        $this->showAssignModal = false;
        $this->assignTechId = null;
    }

    public function cancelBulkAssign(): void
    {
        $this->showAssignModal = false;
        $this->assignTechId = null;
    }

    public function openCreateWo(): void
    {
        $this->newWo = [
            'customer_id' => '',
            'package_id' => '',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'technician_id' => '',
            'notes' => '',
            'priority' => 'medium',
        ];
        $this->showCreateWo = true;
    }

    public function closeCreateWo(): void
    {
        $this->showCreateWo = false;
    }

    public function submitCreateWo(): void
    {
        if (empty($this->newWo['customer_id'])) {
            $this->errorMessage = 'Customer wajib dipilih.';
            return;
        }
        try {
            $wo = new CrmInstallation();
            $wo->customer_id = $this->newWo['customer_id'];
            $wo->scheduled_date = $this->newWo['scheduled_date'];
            $wo->scheduled_time = $this->newWo['scheduled_time'];
            $wo->technician_id = $this->newWo['technician_id'] ?: null;
            $wo->notes = $this->newWo['notes'] ?: '';
            $wo->priority = $this->newWo['priority'] ?? 'medium';
            $wo->status = !empty($this->newWo['technician_id']) ? 'scheduled' : 'pending';
            $wo->created_by = Auth::id() ?? 0;
            $wo->save();
            session()->flash('success', 'Work Order #' . $wo->id . ' berhasil dibuat.');
            $this->showCreateWo = false;
            $this->loadAll();
            $this->resetPage();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal membuat WO: ' . $e->getMessage();
        }
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            if ($tab === 'schedule') $this->loadCalendar();
            if ($tab === 'technician') $this->loadTechnicianLoad();
        }
    }

    public function updatedScheduleDateFrom(): void
    {
        if ($this->activeTab === 'schedule') $this->loadCalendar();
    }

    public function updatedScheduleDateTo(): void
    {
        if ($this->activeTab === 'schedule') $this->loadCalendar();
    }

    public function rowDetail(int $id): void
    {
        try {
            $wo = CrmInstallation::find($id);
            if (!$wo) return;
            $tech = $wo->technician_id ? (User::find($wo->technician_id)?->name ?? '-') : '-';
            $this->confirmTitle = 'Work Order #' . $id;
            $this->confirmMessage = 'Customer: ' . ($wo->customer?->name ?? '-') .
                ' | Jadwal: ' . ($wo->scheduled_date ?? '-') . ' ' . ($wo->scheduled_time ?? '') .
                ' | Teknisi: ' . $tech . ' | Status: ' . strtoupper((string)($wo->status ?? 'pending'));
            $this->confirmAction = '';
            $this->confirmBtnText = 'Tutup';
            $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
            $this->dispatch('open-modal', name: $this->confirmModal);
        } catch (Throwable) {
        }
    }

    public function rowAssign(int $id): void
    {
        $this->selected = [(string)$id];
        $this->assignWoId = $id;
        $this->assignTechId = null;
        $this->showAssignModal = true;
    }

    public function rowStart(int $id): void
    {
        try {
            $wo = CrmInstallation::find($id);
            if ($wo) {
                $wo->status = 'in_progress';
                $wo->save();
                session()->flash('success', 'WO #' . $id . ' dimulai.');
                $this->loadAll();
            }
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal start: ' . $e->getMessage();
        }
    }

    public function rowComplete(int $id): void
    {
        $this->confirmTitle = 'Selesaikan WO #' . $id;
        $this->confirmMessage = 'Anda akan menandai instalasi ini SELESAI. Checklist instalasi sudah lengkap?';
        $this->confirmAction = 'complete-wo';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Selesaikan';
        $this->confirmBtnClass = 'bg-emerald-600 hover:bg-emerald-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function rowCancel(int $id): void
    {
        $this->confirmTitle = 'Batalkan WO #' . $id;
        $this->confirmMessage = 'Anda akan membatalkan Work Order ini. Lanjutkan?';
        $this->confirmAction = 'cancel-wo';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Batalkan';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        $userId = Auth::id() ?? 0;
        if ($this->confirmAction === 'complete-wo') {
            try {
                $id = (int)($this->confirmParams['id'] ?? 0);
                $wo = CrmInstallation::find($id);
                if ($wo) {
                    $wo->status = 'completed';
                    $wo->completed_at = now();
                    $wo->completed_by = $userId;
                    $wo->save();
                    session()->flash('success', 'WO #' . $id . ' selesai.');
                    $this->loadAll();
                }
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal complete: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }
        if ($this->confirmAction === 'cancel-wo') {
            try {
                $id = (int)($this->confirmParams['id'] ?? 0);
                $wo = CrmInstallation::find($id);
                if ($wo) {
                    $wo->status = 'cancelled';
                    $wo->save();
                    session()->flash('success', 'WO #' . $id . ' dibatalkan.');
                    $this->loadAll();
                }
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal cancel: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }
        parent::handleConfirm();
    }

    public function getToolbarActions(): array
    {
        return [
            ['label' => 'WO Baru', 'icon' => 'play', 'action' => 'openCreateWo()'],
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Bulk Assign', 'icon' => 'users', 'action' => "applyBulk('assign')"],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'assign', 'label' => 'Assign Teknisi'],
            ['key' => 'complete', 'label' => 'Complete', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['key' => 'export', 'label' => 'Export'],
            ['key' => 'cancel', 'label' => 'Cancel', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'technician_id', 'label' => 'Teknisi', 'type' => 'select', 'options' => $opts['technicians'] ?? []],
            ['key' => 'customer_id', 'label' => 'Pelanggan', 'type' => 'select', 'options' => $opts['customers'] ?? []],
            ['key' => 'pop_id', 'label' => 'Wilayah / POP', 'type' => 'select', 'options' => $opts['pops'] ?? []],
            ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => $opts['priorities'] ?? []],
            ['key' => 'scheduled_from', 'label' => 'Jadwal (Awal)', 'type' => 'date'],
            ['key' => 'scheduled_to', 'label' => 'Jadwal (Akhir)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Total WO', 'value' => $s['total_wo'] ?? 0, 'color' => 'blue', 'icon' => 'file-text'],
            ['label' => 'Pending', 'value' => $s['pending'] ?? 0, 'color' => 'slate', 'icon' => 'clock'],
            ['label' => 'Scheduled', 'value' => $s['scheduled'] ?? 0, 'color' => 'amber', 'icon' => 'calendar'],
            ['label' => 'In Progress', 'value' => $s['in_progress'] ?? 0, 'color' => 'purple', 'icon' => 'activity'],
            ['label' => 'Selesai', 'value' => $s['completed'] ?? 0, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Jadwal Hari Ini', 'value' => $s['today_scheduled'] ?? 0, 'color' => 'cyan', 'icon' => 'zap'],
        ];
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.support.installation.index', compact('rows'));
    }
}
