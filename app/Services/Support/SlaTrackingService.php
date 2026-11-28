<?php

declare(strict_types=1);

namespace App\Services\Support;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Setting;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaTrackingService
{
    /**
     * Matriks SLA standar berdasarkan prioritas (dalam jam & menit)
     */
    public const PRIORITY_TARGETS_HOURS = [
        'urgent'   => 2,
        'critical' => 2,
        'high'     => 8,
        'medium'   => 24,
        'normal'   => 24,
        'low'      => 48,
    ];

    /**
     * Dapatkan target SLA (menit) berdasarkan prioritas dengan fallback konfigurasi dinamis.
     */
    public function getTargetMinutesByPriority(?string $priority): int
    {
        $normalized = strtolower(trim((string) $priority));
        $defaultHours = self::PRIORITY_TARGETS_HOURS[$normalized] ?? 24;

        // Mendukung penyesuaian dinamis via Setting database
        $configuredHours = (int) Setting::getValue("sla.target.{$normalized}", $defaultHours);
        if ($configuredHours <= 0) {
            $configuredHours = $defaultHours;
        }

        return $configuredHours * 60;
    }

    /**
     * Inisialisasi snapshot SLA pada saat entitas pertama kali dibuat atau dijadwalkan.
     * Snapshot disimpan permanen agar perubahan prioritas di kemudian hari tidak merusak SLA historis.
     */
    public function initializeSla(Model $model, ?Carbon $startTime = null, bool $force = false): void
    {
        if (!$force && !empty($model->sla_due_at) && !empty($model->sla_target_minutes)) {
            return;
        }

        $priority = $model->priority ?? 'normal';
        $targetMinutes = !empty($model->estimated_hours) && (float) $model->estimated_hours > 0
            ? (int) round(((float) $model->estimated_hours) * 60)
            : $this->getTargetMinutesByPriority($priority);

        $start = $startTime;
        if (!$start) {
            if (!empty($model->started_at)) {
                $start = Carbon::parse($model->started_at);
            } elseif (!empty($model->scheduled_at)) {
                $start = Carbon::parse($model->scheduled_at);
            } elseif (!empty($model->scheduled_date)) {
                $time = !empty($model->scheduled_time) ? substr((string) $model->scheduled_time, 0, 5) : '09:00';
                $dateStr = is_string($model->scheduled_date) ? $model->scheduled_date : $model->scheduled_date->toDateString();
                $start = Carbon::parse("{$dateStr} {$time}:00");
            } elseif (!empty($model->created_at)) {
                $start = Carbon::parse($model->created_at);
            } else {
                $start = now();
            }
        }

        $dueAt = $start->copy()->addMinutes($targetMinutes);

        $model->sla_target_minutes = $targetMinutes;
        $model->sla_started_at = $start;
        $model->sla_due_at = $dueAt;
        $model->sla_status = 'normal';

        // Untuk Ticket, sinkronkan juga due_date bawaan jika kosong
        if ($model instanceof Ticket && empty($model->due_date)) {
            $model->due_date = $dueAt->toDateString();
        }

        $model->saveQuietly();
    }

    /**
     * Hitung metrik SLA real-time untuk sebuah pekerjaan.
     */
    public function calculateMetrics(Model $model, ?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : now();

        // Pastikan snapshot SLA sudah ada
        if (empty($model->sla_due_at) || empty($model->sla_target_minutes)) {
            $priority = $model->priority ?? 'normal';
            $targetMinutes = !empty($model->estimated_hours) && (float) $model->estimated_hours > 0
                ? (int) round(((float) $model->estimated_hours) * 60)
                : $this->getTargetMinutesByPriority($priority);

            $start = null;
            if (!empty($model->started_at)) {
                $start = Carbon::parse($model->started_at);
            } elseif (!empty($model->scheduled_at)) {
                $start = Carbon::parse($model->scheduled_at);
            } elseif (!empty($model->scheduled_date)) {
                $time = !empty($model->scheduled_time) ? substr((string) $model->scheduled_time, 0, 5) : '09:00';
                $dateStr = is_string($model->scheduled_date) ? $model->scheduled_date : $model->scheduled_date->toDateString();
                $start = Carbon::parse("{$dateStr} {$time}:00");
            } elseif (!empty($model->created_at)) {
                $start = Carbon::parse($model->created_at);
            } else {
                $start = $now;
            }

            $dueAt = $start->copy()->addMinutes($targetMinutes);
        } else {
            $targetMinutes = (int) $model->sla_target_minutes;
            $start = Carbon::parse($model->sla_started_at ?: $model->created_at ?: $now);
            $dueAt = Carbon::parse($model->sla_due_at);
        }

        // Tentukan apakah pekerjaan sudah selesai final
        $isCompleted = $this->isJobCompleted($model);
        $completedAt = $this->getCompletedAt($model);

        // Jika selesai, waktu evaluasi berhenti pada completed_at
        $evaluationTime = ($isCompleted && $completedAt) ? $completedAt : $now;

        $totalMinutes = max(1, $targetMinutes);
        $elapsedMinutes = max(0, (int) $start->diffInMinutes($evaluationTime, false));
        $remainingMinutes = (int) $evaluationTime->diffInMinutes($dueAt, false); // Positif jika belum lewat, negatif jika terlambat

        // Persentase pemakaian waktu SLA
        $percentageUsed = round(($elapsedMinutes / $totalMinutes) * 100, 2);

        // Tentukan status SLA
        if ($isCompleted) {
            $status = ($completedAt && $completedAt->gt($dueAt)) ? 'breached' : 'completed';
        } elseif ($evaluationTime->gt($dueAt)) {
            $status = 'breached';
        } elseif ($percentageUsed >= 80.0) {
            $status = 'warning';
        } else {
            $status = 'normal';
        }

        // Label format sisa waktu
        $remainingFormatted = $this->formatRemainingTime($remainingMinutes);

        // Badge styling visual
        $badgeColor = match ($status) {
            'completed' => 'slate',
            'normal'    => 'emerald',
            'warning'   => 'amber',
            'breached'  => 'rose',
            default     => 'blue',
        };

        return [
            'target_minutes'      => $totalMinutes,
            'target_hours'        => round($totalMinutes / 60, 1),
            'started_at'          => $start,
            'due_at'              => $dueAt,
            'completed_at'        => $completedAt,
            'is_completed'        => $isCompleted,
            'elapsed_minutes'     => $elapsedMinutes,
            'remaining_minutes'   => $remainingMinutes,
            'remaining_formatted' => $remainingFormatted,
            'percentage_used'     => $percentageUsed,
            'status'              => $status,
            'badge_color'         => $badgeColor,
            'server_now'          => $now,
            'server_now_unix'     => $now->timestamp,
            'due_at_unix'         => $dueAt->timestamp,
            'started_at_unix'     => $start->timestamp,
        ];
    }

    /**
     * Evaluasi dan sinkronkan status SLA ke database.
     */
    public function evaluateAndSaveStatus(Model $model, ?Carbon $now = null): string
    {
        $metrics = $this->calculateMetrics($model, $now);
        $newStatus = $metrics['status'];

        if ($model->sla_status !== $newStatus) {
            $model->sla_status = $newStatus;
            $model->saveQuietly();
        }

        return $newStatus;
    }

    /**
     * Tandai pekerjaan selesai dan kunci evaluasi SLA.
     */
    public function completeSla(Model $model, ?Carbon $completedAt = null): void
    {
        $completedAt = $completedAt ?: now();
        $metrics = $this->calculateMetrics($model, $completedAt);

        // Jika selesai setelah due date, status tercatat breached (terlambat), selain itu completed (tepat waktu)
        $finalStatus = ($completedAt->gt($metrics['due_at'])) ? 'breached' : 'completed';

        $model->sla_status = $finalStatus;
        $model->saveQuietly();
    }

    /**
     * Periksa apakah pekerjaan telah berstatus selesai final.
     * PENTING: pending_approval BUKAN completed (SLA tetap berjalan sampai persetujuan QC final).
     */
    public function isJobCompleted(Model $model): bool
    {
        $status = strtolower(trim((string) ($model->status ?? '')));

        if ($model instanceof WorkOrder) {
            return in_array($status, ['completed', 'cancelled'], true);
        }

        if ($model instanceof Ticket) {
            return in_array($status, ['resolved', 'closed', 'cancelled'], true);
        }

        if ($model instanceof Installation) {
            return in_array($status, ['completed', 'verified', 'active', 'cancelled'], true);
        }

        return in_array($status, ['completed', 'resolved', 'closed', 'cancelled'], true);
    }

    /**
     * Dapatkan timestamp penyelesaian pekerjaan.
     */
    public function getCompletedAt(Model $model): ?Carbon
    {
        if ($model instanceof WorkOrder) {
            return $model->approved_at ?: ($model->completed_at ?: null);
        }

        if ($model instanceof Ticket) {
            return $model->resolved_at ?: ($model->updated_at ?: null);
        }

        if ($model instanceof Installation) {
            return $model->completed_at ?: ($model->updated_at ?: null);
        }

        return $model->completed_at ?? ($model->resolved_at ?? null);
    }

    /**
     * Format sisa waktu menjadi teks ringkas dan informatif.
     */
    public function formatRemainingTime(int $remainingMinutes): string
    {
        if ($remainingMinutes < 0) {
            $abs = abs($remainingMinutes);
            $hours = intdiv($abs, 60);
            $mins = $abs % 60;
            return $hours > 0 ? "Terlambat {$hours}j {$mins}m" : "Terlambat {$mins}m";
        }

        $hours = intdiv($remainingMinutes, 60);
        $mins = $remainingMinutes % 60;

        if ($hours > 0) {
            return "{$hours}j {$mins}m";
        }

        return "{$mins}m";
    }

    /**
     * Dapatkan pengenal operasional tunggal (SSOT Key) untuk mencegah duplikasi.
     */
    public function getOperationalKey(Model $model): string
    {
        if ($model instanceof WorkOrder) {
            return "wo_{$model->id}";
        }
        if ($model instanceof Ticket) {
            return "ticket_{$model->id}";
        }
        if ($model instanceof Installation) {
            return "inst_{$model->id}";
        }

        return strtolower(class_basename($model)) . "_{$model->id}";
    }

    /**
     * Dapatkan seluruh pekerjaan aktif unik lintas entitas (WorkOrder, Ticket, Installation)
     * tanpa duplikasi untuk monitoring SLA dan eskalasi.
     */
    public function getActiveJobsMonitoring(array $filters = [], ?User $user = null): Collection
    {
        return $this->getUniqueActiveJobs($user, $filters);
    }

    public function getUniqueActiveJobs(?User $user = null, array $filters = []): Collection
    {
        $jobs = collect();

        // 1. Ambil Work Orders aktif (SSOT Pekerjaan Lapangan)
        $woQuery = WorkOrder::withoutGlobalScopes()
            ->with(['customer', 'technician', 'branch', 'reseller', 'ticket', 'installation'])
            ->whereNotIn('status', ['completed', 'cancelled']);

        $this->applyUserScopes($woQuery, $user);
        $this->applyFilters($woQuery, $filters);

        $workOrders = $woQuery->get();
        $handledTicketIds = [];
        $handledInstallationIds = [];

        foreach ($workOrders as $wo) {
            if ($wo->ticket_id) {
                $handledTicketIds[] = (int) $wo->ticket_id;
            }
            if ($wo->installation_id) {
                $handledInstallationIds[] = (int) $wo->installation_id;
            }

            $jobs->push((object) [
                'operational_key' => "wo_{$wo->id}",
                'entity_type'     => 'work_order',
                'entity_id'       => $wo->id,
                'model'           => $wo,
                'code'            => $wo->uuid ? ('SPK-' . strtoupper(substr(str_replace('-', '', $wo->uuid), 0, 6))) : ('SPK-' . $wo->id),
                'type'            => $wo->type ?? 'work_order',
                'title'           => $wo->title ?: ('Work Order #' . $wo->id),
                'priority'        => $wo->priority ?? 'normal',
                'status'          => $wo->status,
                'customer_name'   => $wo->customer?->name ?? 'Pelanggan',
                'technician_name' => $wo->technician?->name ?? 'Belum Ditugaskan',
                'technician_id'   => $wo->assigned_to,
                'branch_id'       => $wo->branch_id,
                'branch_name'     => $wo->branch?->name ?? 'Pusat',
                'reseller_id'     => $wo->reseller_id,
                'company_id'      => $wo->company_id,
                'metrics'         => $this->calculateMetrics($wo),
            ]);
        }

        // 2. Ambil Ticket aktif yang BELUM memiliki WorkOrder lapangan
        $ticketQuery = Ticket::withoutGlobalScopes()
            ->with(['customer', 'assignedTo', 'branch', 'reseller'])
            ->whereNotIn('status', ['resolved', 'closed', 'cancelled']);

        if (!empty($handledTicketIds)) {
            $ticketQuery->whereNotIn('id', array_unique($handledTicketIds));
        }

        $this->applyUserScopes($ticketQuery, $user);
        $this->applyFilters($ticketQuery, $filters);

        $tickets = $ticketQuery->get();
        foreach ($tickets as $ticket) {
            $jobs->push((object) [
                'operational_key' => "ticket_{$ticket->id}",
                'entity_type'     => 'ticket',
                'entity_id'       => $ticket->id,
                'model'           => $ticket,
                'code'            => $ticket->uuid ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id),
                'type'            => 'ticket',
                'title'           => $ticket->title ?: ('Tiket Support #' . $ticket->id),
                'priority'        => $ticket->priority ?? 'normal',
                'status'          => $ticket->status,
                'customer_name'   => $ticket->customer?->name ?? 'Pelanggan',
                'technician_name' => $ticket->assignedTo?->name ?? 'Belum Ditugaskan',
                'technician_id'   => $ticket->assigned_to,
                'branch_id'       => $ticket->branch_id,
                'branch_name'     => $ticket->branch?->name ?? 'Pusat',
                'reseller_id'     => $ticket->reseller_id,
                'company_id'      => $ticket->company_id ?? null,
                'metrics'         => $this->calculateMetrics($ticket),
            ]);
        }

        // 3. Ambil Installation aktif yang BELUM memiliki WorkOrder
        $instQuery = Installation::withoutGlobalScopes()
            ->with(['customer', 'assignedTo', 'technician', 'branch'])
            ->whereNotIn('status', ['completed', 'verified', 'active', 'cancelled']);

        if (!empty($handledInstallationIds)) {
            $instQuery->whereNotIn('id', array_unique($handledInstallationIds));
        }

        $this->applyUserScopes($instQuery, $user);
        $this->applyFilters($instQuery, $filters);

        $installations = $instQuery->get();
        foreach ($installations as $inst) {
            $techUser = $inst->technician ?: $inst->assignedTo;
            $jobs->push((object) [
                'operational_key' => "inst_{$inst->id}",
                'entity_type'     => 'installation',
                'entity_id'       => $inst->id,
                'model'           => $inst,
                'code'            => $inst->customer?->code ?? ('PSB-' . $inst->id),
                'type'            => 'installation',
                'title'           => 'Instalasi Pasang Baru #' . $inst->id,
                'priority'        => $inst->priority ?? 'normal',
                'status'          => $inst->status,
                'customer_name'   => $inst->customer_name ?: ($inst->customer?->name ?? 'Calon Pelanggan'),
                'technician_name' => $techUser?->name ?? 'Belum Ditugaskan',
                'technician_id'   => $inst->assigned_to ?: $inst->technician_id,
                'branch_id'       => $inst->branch_id,
                'branch_name'     => $inst->branch?->name ?? 'Pusat',
                'reseller_id'     => $inst->reseller_id,
                'company_id'      => null,
                'metrics'         => $this->calculateMetrics($inst),
            ]);
        }

        return $jobs;
    }

    /**
     * Dapatkan Laporan Kinerja SLA Operasional Terpadu (Single Operational View).
     */
    public function getSlaReport(array $filters = [], ?User $user = null): array
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : now()->endOfDay();

        // 1. Ambil Work Orders dalam periode
        $woQuery = WorkOrder::withoutGlobalScopes()
            ->with(['technician', 'branch', 'reseller'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        $this->applyUserScopes($woQuery, $user);
        $this->applyFilters($woQuery, $filters);
        $workOrders = $woQuery->get();

        $handledTicketIds = [];
        $handledInstIds = [];
        $uniqueJobs = collect();

        foreach ($workOrders as $wo) {
            if ($wo->ticket_id) $handledTicketIds[] = (int) $wo->ticket_id;
            if ($wo->installation_id) $handledInstIds[] = (int) $wo->installation_id;
            $uniqueJobs->push([
                'model'           => $wo,
                'entity_type'     => 'work_order',
                'metrics'         => $this->calculateMetrics($wo),
                'branch_id'       => $wo->branch_id,
                'branch_name'     => $wo->branch?->name ?? 'Pusat',
                'reseller_id'     => $wo->reseller_id,
                'reseller_name'   => $wo->reseller?->name ?? 'Direct',
                'technician_id'   => $wo->assigned_to,
                'technician_name' => $wo->technician?->name ?? 'Belum Ditugaskan',
                'priority'        => $wo->priority ?? 'normal',
            ]);
        }

        // 2. Ambil Ticket mandiri dalam periode
        $tktQuery = Ticket::withoutGlobalScopes()
            ->with(['assignedTo', 'branch', 'reseller'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!empty($handledTicketIds)) {
            $tktQuery->whereNotIn('id', array_unique($handledTicketIds));
        }
        $this->applyUserScopes($tktQuery, $user);
        $this->applyFilters($tktQuery, $filters);
        $tickets = $tktQuery->get();

        foreach ($tickets as $tkt) {
            $uniqueJobs->push([
                'model'           => $tkt,
                'entity_type'     => 'ticket',
                'metrics'         => $this->calculateMetrics($tkt),
                'branch_id'       => $tkt->branch_id,
                'branch_name'     => $tkt->branch?->name ?? 'Pusat',
                'reseller_id'     => $tkt->reseller_id,
                'reseller_name'   => $tkt->reseller?->name ?? 'Direct',
                'technician_id'   => $tkt->assigned_to,
                'technician_name' => $tkt->assignedTo?->name ?? 'Belum Ditugaskan',
                'priority'        => $tkt->priority ?? 'normal',
            ]);
        }

        // 3. Ambil Installation mandiri dalam periode
        $instQuery = Installation::withoutGlobalScopes()
            ->with(['technician', 'assignedTo', 'branch'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!empty($handledInstIds)) {
            $instQuery->whereNotIn('id', array_unique($handledInstIds));
        }
        $this->applyUserScopes($instQuery, $user);
        $this->applyFilters($instQuery, $filters);
        $installations = $instQuery->get();

        foreach ($installations as $inst) {
            $tech = $inst->technician ?: $inst->assignedTo;
            $uniqueJobs->push([
                'model'           => $inst,
                'entity_type'     => 'installation',
                'metrics'         => $this->calculateMetrics($inst),
                'branch_id'       => $inst->branch_id,
                'branch_name'     => $inst->branch?->name ?? 'Pusat',
                'reseller_id'     => $inst->reseller_id,
                'reseller_name'   => 'Direct',
                'technician_id'   => $inst->assigned_to ?: $inst->technician_id,
                'technician_name' => $tech?->name ?? 'Belum Ditugaskan',
                'priority'        => $inst->priority ?? 'normal',
            ]);
        }

        // Hitung ringkasan metrik
        $total = $uniqueJobs->count();
        $completedMet = 0;
        $completedBreached = 0;
        $activeNormal = 0;
        $activeWarning = 0;
        $activeBreached = 0;

        $totalResolutionMinutes = 0;
        $totalOverdueMinutes = 0;

        // Breakdown aggregators
        $byBranch = [];
        $byReseller = [];
        $byTechnician = [];
        $byPriority = [];

        foreach ($uniqueJobs as $item) {
            $m = $item['metrics'];
            $st = $m['status'];
            $isComp = $m['is_completed'];

            if ($isComp) {
                if ($st === 'breached') {
                    $completedBreached++;
                    $totalOverdueMinutes += abs($m['remaining_minutes']);
                } else {
                    $completedMet++;
                }
                $totalResolutionMinutes += $m['elapsed_minutes'];
            } else {
                if ($st === 'breached') {
                    $activeBreached++;
                    $totalOverdueMinutes += abs($m['remaining_minutes']);
                } elseif ($st === 'warning') {
                    $activeWarning++;
                } else {
                    $activeNormal++;
                }
            }

            // Grouping: Branch
            $brKey = $item['branch_name'];
            $byBranch[$brKey] = $byBranch[$brKey] ?? ['total' => 0, 'met' => 0, 'breached' => 0];
            $byBranch[$brKey]['total']++;
            if ($st === 'breached') $byBranch[$brKey]['breached']++;
            else $byBranch[$brKey]['met']++;

            // Grouping: Reseller
            $resKey = $item['reseller_name'];
            $byReseller[$resKey] = $byReseller[$resKey] ?? ['total' => 0, 'met' => 0, 'breached' => 0];
            $byReseller[$resKey]['total']++;
            if ($st === 'breached') $byReseller[$resKey]['breached']++;
            else $byReseller[$resKey]['met']++;

            // Grouping: Technician
            $techKey = $item['technician_name'];
            $byTechnician[$techKey] = $byTechnician[$techKey] ?? ['total' => 0, 'met' => 0, 'breached' => 0];
            $byTechnician[$techKey]['total']++;
            if ($st === 'breached') $byTechnician[$techKey]['breached']++;
            else $byTechnician[$techKey]['met']++;

            // Grouping: Priority
            $prioKey = ucfirst(strtolower((string) $item['priority']));
            $byPriority[$prioKey] = $byPriority[$prioKey] ?? ['total' => 0, 'met' => 0, 'breached' => 0];
            $byPriority[$prioKey]['total']++;
            if ($st === 'breached') $byPriority[$prioKey]['breached']++;
            else $byPriority[$prioKey]['met']++;
        }

        $totalBreached = $completedBreached + $activeBreached;
        $totalMet = $completedMet + $activeNormal + $activeWarning;
        $complianceRate = $total > 0 ? round(($totalMet / $total) * 100, 1) : 100.0;

        $completedCount = $completedMet + $completedBreached;
        $avgResolutionHours = $completedCount > 0 ? round(($totalResolutionMinutes / $completedCount) / 60, 1) : 0;
        $avgOverdueHours = $totalBreached > 0 ? round(($totalOverdueMinutes / $totalBreached) / 60, 1) : 0;

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
            ],
            'total_jobs'           => $total,
            'completed_jobs'       => $completedCount,
            'active_jobs'          => $total - $completedCount,
            'met_sla'              => $totalMet,
            'warning_jobs'         => $activeWarning,
            'breached_jobs'        => $totalBreached,
            'compliance_rate'      => $complianceRate,
            'avg_resolution_hours' => $avgResolutionHours,
            'avg_overdue_hours'    => $avgOverdueHours,
            'by_branch'            => $byBranch,
            'by_reseller'          => $byReseller,
            'by_technician'        => $byTechnician,
            'by_priority'          => $byPriority,
        ];
    }

    /**
     * Terapkan scope pengguna (CompanyScope, BranchScope, ResellerScope).
     */
    protected function applyUserScopes($query, ?User $user): void
    {
        if (!$user) return;

        // Branch Isolation: Jika user terikat pada cabang tertentu, batasi query hanya untuk cabang tersebut
        if (!empty($user->branch_id)) {
            $query->where('branch_id', $user->branch_id);
        }

        // Reseller Isolation: Jika user adalah reseller atau teknisi kemitraan reseller
        $role = strtolower((string) ($user->role ?? $user->job_function ?? ''));
        if ($role === 'reseller' || !empty($user->reseller_id)) {
            $targetResellerId = $role === 'reseller' ? $user->id : $user->reseller_id;
            $query->where('reseller_id', $targetResellerId);
        }
    }

    /**
     * Terapkan filter opsional (branch, reseller, priority, date).
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['reseller_id'])) {
            if ($filters['reseller_id'] === 'direct') {
                $query->whereNull('reseller_id');
            } else {
                $query->where('reseller_id', $filters['reseller_id']);
            }
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
    }
}
