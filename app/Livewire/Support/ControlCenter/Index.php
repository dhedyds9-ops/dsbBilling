<?php

declare(strict_types=1);

namespace App\Livewire\Support\ControlCenter;

use App\Livewire\AdminComponent;
use App\Models\AuditLog;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\GPSLog;
use App\Models\Workforce\WorkOrder;
use App\Services\Support\SlaTrackingService;
use App\Services\Workforce\UnifiedQcApprovalService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    // Filters & Navigation
    public string $dateRange = 'today';
    public $branchFilter = '';
    public $resellerFilter = '';
    public string $activeStatusTab = 'all'; // 'all', 'breached', 'warning', 'qc_pending', 'completed'
    public string $searchQuery = '';
    public bool $showMap = true;
    public bool $filterTechOnlyWithJobs = false;

    // Map Layers
    public bool $layerTechnicians = true;
    public bool $layerJobs = true;

    protected $queryString = [
        'dateRange' => ['except' => 'today'],
        'branchFilter' => ['except' => ''],
        'activeStatusTab' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && !empty($user->branch_id)) {
            $this->branchFilter = (string) $user->branch_id;
        }

        $this->activeModule = 'support';
        $this->activePage = 'control-center';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Support & Field Service', 'url' => route('support.ticket')],
            ['label' => 'Operational Control Center', 'url' => route('support.control-center')],
        ];
    }

    public function setStatusTab(string $tab): void
    {
        $this->activeStatusTab = $tab;
        $this->resetPage();
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->resetPage();
    }

    public function toggleMap(): void
    {
        $this->showMap = !$this->showMap;
    }

    public function refreshData(): void
    {
        // Polling hook for live 30s updates
    }

    public function render(SlaTrackingService $slaService, UnifiedQcApprovalService $qcService)
    {
        $user = Auth::user();

        // 1. Resolve Date Range
        [$startDate, $endDate] = match ($this->dateRange) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $filters = [
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'branch_id'  => $this->branchFilter ?: null,
            'reseller_id'=> $this->resellerFilter ?: null,
        ];

        // 2. Fetch Active Jobs via SSOT SlaTrackingService
        $allActiveJobs = $slaService->getActiveJobsMonitoring($filters, $user);

        // 3. Compute High-Level KPI Counters
        $totalActiveJobs = $allActiveJobs->count();
        $pendingQcCount = $allActiveJobs->where('status', 'pending_approval')->count();

        $slaBreachedCount = 0;
        $slaWarningCount = 0;
        foreach ($allActiveJobs as $job) {
            $metrics = $job->metrics ?? [];
            if (!empty($metrics['is_breached']) || ($metrics['status'] ?? '') === 'breached') {
                $slaBreachedCount++;
            } elseif (!empty($metrics['is_warning']) || ($metrics['status'] ?? '') === 'warning') {
                $slaWarningCount++;
            }
        }

        // 4. Fetch Technicians On Duty (Attendance + Latest GPS)
        $techniciansData = $this->getTechniciansOnDuty($user);

        // 5. Calculate SLA Compliance Rate for historical/active view
        $slaReport = $slaService->getSlaReport($filters, $user);
        $slaComplianceRate = $slaReport['compliance_rate'] ?? 100.0;
        $totalResolvedJobs = $slaReport['total_jobs'] ?? 0;

        // 6. Filter Jobs for Table & Map based on activeStatusTab and searchQuery
        $filteredJobs = $this->filterJobs($allActiveJobs, $this->activeStatusTab, $this->searchQuery);

        // 7. Prepare Leaflet Map Markers (JSON serializable)
        $mapMarkers = $this->buildMapMarkers($filteredJobs, $techniciansData['technicians']);

        // 8. Performance Breakdown by Branch & Technician
        $branchBreakdown = $slaReport['by_branch'] ?? [];
        $technicianLeaderboard = $this->getTechnicianLeaderboard($filters, $user);

        // 9. Recent Activity Trail (AuditLog for Field Work)
        $recentActivities = $this->getRecentActivities($user);

        // 10. Available Branches & Resellers for dropdown filters
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $resellers = User::whereHas('roles', fn($q) => $q->where('name', 'reseller'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.support.control-center.index', [
            'totalActiveJobs'       => $totalActiveJobs,
            'techsOnDutyCount'      => $techniciansData['on_duty_count'],
            'techsTotalCount'       => $techniciansData['total_count'],
            'pendingQcCount'        => $pendingQcCount,
            'slaBreachedCount'      => $slaBreachedCount,
            'slaWarningCount'       => $slaWarningCount,
            'slaComplianceRate'     => $slaComplianceRate,
            'totalResolvedJobs'     => $totalResolvedJobs,
            'jobs'                  => $filteredJobs,
            'technicians'           => $techniciansData['technicians'],
            'mapMarkers'            => $mapMarkers,
            'branchBreakdown'       => $branchBreakdown,
            'technicianLeaderboard' => $technicianLeaderboard,
            'recentActivities'      => $recentActivities,
            'branches'              => $branches,
            'resellers'             => $resellers,
        ]);
    }

    /**
     * Dapatkan daftar teknisi yang sedang bertugas hari ini beserta koordinat GPS terakhir.
     */
    protected function getTechniciansOnDuty(?User $currentUser): array
    {
        $techQuery = User::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', ['technician', 'teknisi']))
                  ->orWhere('job_function', 'TECHNICIAN');
            })
            ->where('is_active', true)
            ->with(['branch']);

        if ($this->branchFilter) {
            $techQuery->where('branch_id', (int) $this->branchFilter);
        } elseif ($currentUser && $currentUser->branch_id && !$currentUser->isSuperAdmin()) {
            $techQuery->where('branch_id', $currentUser->branch_id);
        }

        $technicians = $techQuery->get();
        $totalCount = $technicians->count();
        $onDutyCount = 0;

        $today = now()->toDateString();
        $techIds = $technicians->pluck('id')->toArray();

        // 1. Ambil data absensi hari ini
        $attendances = Attendance::whereIn('technician_id', $techIds)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('technician_id');

        // 2. Ambil GPSLog terbaru dalam 12 jam terakhir
        $recentGpsLogs = GPSLog::whereIn('user_id', $techIds)
            ->where('logged_at', '>=', now()->subHours(12))
            ->orderBy('logged_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(fn($logs) => $logs->first());

        // 3. Hitung pekerjaan aktif per teknisi
        $activeJobCounts = WorkOrder::withoutGlobalScopes()
            ->whereIn('assigned_to', $techIds)
            ->whereNotIn('status', ['completed', 'approved', 'cancelled'])
            ->select('assigned_to', DB::raw('count(*) as total'))
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $resultTechs = [];

        foreach ($technicians as $tech) {
            $att = $attendances->get($tech->id);
            $gps = $recentGpsLogs->get($tech->id);
            $activeJobs = (int) ($activeJobCounts->get($tech->id) ?? 0);

            $isCheckedIn = !empty($att?->checked_in_at);
            $isWorking = $isCheckedIn && empty($att?->checked_out_at);

            if ($isWorking) {
                $onDutyCount++;
            }

            // Tentukan koordinat: prioritaskan GPSLog terbaru, fallback ke check_in absensi
            $lat = $gps?->latitude ?: ($att?->check_in_latitude ?: null);
            $lng = $gps?->longitude ?: ($att?->check_in_longitude ?: null);
            $lastSeen = $gps?->logged_at ?: ($att?->checked_in_at ?: null);

            $resultTechs[] = [
                'id'            => $tech->id,
                'name'          => $tech->name,
                'phone'         => $tech->whatsapp ?: $tech->phone,
                'branch_id'     => $tech->branch_id,
                'branch_name'   => $tech->branch?->name ?? 'Pusat',
                'is_on_duty'    => $isWorking,
                'status_label'  => $isWorking ? 'Bertugas' : ($isCheckedIn ? 'Selesai Shift' : 'Belum Absen'),
                'checked_in_at' => $att?->checked_in_at?->format('H:i'),
                'active_jobs'   => $activeJobs,
                'latitude'      => $lat ? (float) $lat : null,
                'longitude'     => $lng ? (float) $lng : null,
                'last_seen_at'  => $lastSeen?->diffForHumans(),
            ];
        }

        return [
            'total_count'   => $totalCount,
            'on_duty_count' => $onDutyCount,
            'technicians'   => collect($resultTechs),
        ];
    }

    /**
     * Filter daftar pekerjaan aktif berdasarkan tab status dan kata kunci pencarian.
     */
    protected function filterJobs(Collection $jobs, string $tab, string $search): Collection
    {
        $filtered = $jobs;

        // 1. Filter Tab Status
        $filtered = match ($tab) {
            'breached' => $filtered->filter(fn($j) => !empty($j->metrics['is_breached']) || ($j->metrics['status'] ?? '') === 'breached'),
            'warning' => $filtered->filter(fn($j) => !empty($j->metrics['is_warning']) || ($j->metrics['status'] ?? '') === 'warning'),
            'qc_pending' => $filtered->filter(fn($j) => $j->status === 'pending_approval'),
            'completed' => $filtered->filter(fn($j) => in_array($j->status, ['completed', 'approved', 'verified'], true)),
            default => $filtered,
        };

        // 2. Filter Search Query
        if (!empty(trim($search))) {
            $q = strtolower(trim($search));
            $filtered = $filtered->filter(function ($j) use ($q) {
                return str_contains(strtolower((string) $j->code), $q)
                    || str_contains(strtolower((string) $j->title), $q)
                    || str_contains(strtolower((string) $j->customer_name), $q)
                    || str_contains(strtolower((string) $j->technician_name), $q)
                    || str_contains(strtolower((string) $j->branch_name), $q);
            });
        }

        return $filtered->values();
    }

    /**
     * Bangun marker JSON untuk Leaflet Map.
     */
    protected function buildMapMarkers(Collection $jobs, Collection $technicians): array
    {
        $markers = [];

        // 1. Marker Teknisi
        if ($this->layerTechnicians) {
            foreach ($technicians as $tech) {
                if ($tech['latitude'] && $tech['longitude']) {
                    $markers[] = [
                        'type'        => 'technician',
                        'id'          => $tech['id'],
                        'title'       => $tech['name'],
                        'lat'         => $tech['latitude'],
                        'lng'         => $tech['longitude'],
                        'is_on_duty'  => (bool) $tech['is_on_duty'],
                        'status'      => $tech['is_on_duty'] ? 'online' : 'offline',
                        'branch'      => $tech['branch_name'],
                        'phone'       => $tech['phone'],
                        'active_jobs' => $tech['active_jobs'],
                        'last_seen'   => $tech['last_seen_at'] ?? 'Hari ini',
                        'color'       => $tech['is_on_duty'] ? '#10B981' : '#9CA3AF',
                        'icon'        => 'engineering',
                    ];
                }
            }
        }

        // 2. Marker Pekerjaan Lapangan (WorkOrder / PSB / Tiket)
        if ($this->layerJobs) {
            foreach ($jobs as $job) {
                $model = $job->model ?? null;
                $lat = null;
                $lng = null;

                if ($model instanceof WorkOrder) {
                    $lat = $model->customer?->latitude ?: ($model->installation?->latitude ?: null);
                    $lng = $model->customer?->longitude ?: ($model->installation?->longitude ?: null);
                } elseif ($model instanceof Installation) {
                    $lat = $model->latitude ?: ($model->customer?->latitude ?: null);
                    $lng = $model->longitude ?: ($model->customer?->longitude ?: null);
                } elseif ($model instanceof Ticket) {
                    $lat = $model->customer?->latitude ?: null;
                    $lng = $model->customer?->longitude ?: null;
                }

                if ($lat && $lng) {
                    $metrics = $job->metrics ?? [];
                    $isBreached = !empty($metrics['is_breached']) || ($metrics['status'] ?? '') === 'breached';
                    $isWarning = !empty($metrics['is_warning']) || ($metrics['status'] ?? '') === 'warning';
                    $isQcPending = $job->status === 'pending_approval';

                    $color = match (true) {
                        $isBreached  => '#EF4444', // Red Danger
                        $isWarning   => '#F59E0B', // Amber Warning
                        $isQcPending => '#8B5CF6', // Purple QC
                        default      => '#0866F5', // Blue Normal
                    };

                    $actionUrl = match ($job->entity_type) {
                        'ticket' => route('tickets.direct', $job->entity_id),
                        default => route('support.work-orders.history'),
                    };

                    $markers[] = [
                        'type'       => 'job',
                        'id'         => $job->entity_id,
                        'code'       => $job->code,
                        'title'      => $job->title,
                        'customer'   => $job->customer_name,
                        'technician' => $job->technician_name,
                        'lat'        => (float) $lat,
                        'lng'        => (float) $lng,
                        'status'     => $job->status,
                        'priority'   => strtoupper($job->priority ?? 'NORMAL'),
                        'sla_text'     => $metrics['remaining_formatted'] ?? '-',
                        'color'        => $color,
                        'marker_color' => $color,
                        'action_url'   => $actionUrl,
                        'icon'       => match ($job->type) {
                            'installation' => 'router',
                            'troubleshoot' => 'build',
                            'maintenance'  => 'handyman',
                            default        => 'assignment',
                        },
                    ];
                }
            }
        }

        return $markers;
    }

    /**
     * Dapatkan leaderboard teknisi dengan performa penyelesaian dan SLA.
     */
    protected function getTechnicianLeaderboard(array $filters, ?User $user): array
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : now()->startOfMonth();
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : now()->endOfDay();

        $woQuery = WorkOrder::withoutGlobalScopes()
            ->with(['technician'])
            ->whereNotNull('assigned_to')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (!empty($filters['branch_id'])) {
            $woQuery->where('branch_id', (int) $filters['branch_id']);
        }

        $workOrders = $woQuery->get();

        $leaderboard = [];
        foreach ($workOrders as $wo) {
            $techId = $wo->assigned_to;
            if (!$techId) continue;

            if (!isset($leaderboard[$techId])) {
                $leaderboard[$techId] = [
                    'id'          => $techId,
                    'name'        => $wo->technician?->name ?? "Teknisi #{$techId}",
                    'total'       => 0,
                    'completed'   => 0,
                    'breached'    => 0,
                    'in_progress' => 0,
                ];
            }

            $leaderboard[$techId]['total']++;
            if (in_array($wo->status, ['completed', 'approved'], true)) {
                $leaderboard[$techId]['completed']++;
            } elseif ($wo->status === 'in_progress') {
                $leaderboard[$techId]['in_progress']++;
            }

            if ($wo->sla_status === 'breached' || ($wo->sla_due_at && $wo->completed_at && $wo->completed_at->gt($wo->sla_due_at))) {
                $leaderboard[$techId]['breached']++;
            }
        }

        // Hitung compliance rate dan urutkan
        foreach ($leaderboard as &$tech) {
            $tech['compliance_rate'] = $tech['total'] > 0 
                ? round((($tech['total'] - $tech['breached']) / $tech['total']) * 100, 1) 
                : 100.0;
        }
        unset($tech);

        uasort($leaderboard, fn($a, $b) => $b['completed'] <=> $a['completed'] ?: $b['compliance_rate'] <=> $a['compliance_rate']);

        return array_slice($leaderboard, 0, 5, true);
    }

    /**
     * Dapatkan log aktivitas dan audit trail pekerjaan lapangan terakhir.
     */
    protected function getRecentActivities(?User $user): Collection
    {
        return AuditLog::query()
            ->whereIn('auditable_type', [
                WorkOrder::class,
                Ticket::class,
                Installation::class,
                SupportMaintenance::class,
            ])
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();
    }
}
