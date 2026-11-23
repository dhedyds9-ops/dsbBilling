<?php

declare(strict_types=1);

namespace App\Services\Workforce;

use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Models\AuditLog;
use App\Models\CRM\Installation;
use App\Models\CRM\QualityControl;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * SSOT: Unified Quality Control Approval Service for all field work in dsBilling Enterprise.
 *
 * Mengatur satu siklus persetujuan terpusat untuk:
 * - Work Order SPK
 * - Instalasi Pasang Baru (PSB)
 * - Pemeliharaan Jaringan (SupportMaintenance)
 * - Tiket Perbaikan Lapangan (Ticket)
 */
class UnifiedQcApprovalService
{
    /**
     * Periksa apakah pengguna memiliki hak untuk menyetujui / memeriksa QC pekerjaan.
     */
    public function canApprove(WorkOrder $workOrder, User $user): bool
    {
        if (!$user->is_active) {
            return false;
        }

        // 1. Otorisasi Peran: Harus Administrator, Superadmin, Admin, NOC, Manager, Owner, atau Root
        $isSupervisor = $user->hasRole(['administrator', 'superadmin', 'admin', 'noc', 'manager', 'owner', 'root'])
            || in_array(strtoupper((string) $user->job_function), ['NOC', 'MANAGER', 'ADMINISTRATOR', 'ROOT'], true);

        if (!$isSupervisor) {
            return false;
        }

        // 2. Proteksi Self-Approval: Teknisi DILARANG menyetujui pekerjaannya sendiri!
        if ($workOrder->assigned_to && (int) $workOrder->assigned_to === (int) $user->id) {
            return false;
        }
        if (is_array($workOrder->technician_ids) && in_array($user->id, $workOrder->technician_ids, true)) {
            return false;
        }

        // 3. Isolasi Multi-Tenant Cabang (BranchScope)
        if ($user->branch_id && $workOrder->branch_id && (int) $user->branch_id !== (int) $workOrder->branch_id) {
            return false;
        }

        // 4. Isolasi Multi-Tenant Reseller (ResellerScope)
        if ($user->isReseller()) {
            $userResellerId = $user->getEffectiveResellerId();
            if ($userResellerId && (int) $workOrder->reseller_id !== (int) $userResellerId) {
                return false;
            }
        }

        // 5. Validasi Status: Hanya pekerjaan pending_approval atau in_progress (override oleh supervisor)
        return in_array($workOrder->status, ['pending_approval', 'in_progress'], true);
    }

    /**
     * Periksa apakah pengguna memiliki hak untuk menolak QC pekerjaan.
     */
    public function canReject(WorkOrder $workOrder, User $user): bool
    {
        return $this->canApprove($workOrder, $user);
    }

    /**
     * Setujui pekerjaan lapangan (QC Passed & Approved).
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function approve(WorkOrder $workOrder, User $approver, ?string $notes = null): WorkOrder
    {
        // 0. IDEMPOTENCY PRE-CHECK: Jika sudah completed dan approved_at ada, kembalikan langsung tanpa error
        if ($workOrder->status === 'completed' && $workOrder->approved_at !== null) {
            return $workOrder;
        }

        // 1. Guard Otorisasi & Self-Approval
        if ($workOrder->assigned_to && (int) $workOrder->assigned_to === (int) $approver->id) {
            throw new AuthorizationException('Teknisi tidak dapat menyetujui pekerjaannya sendiri.');
        }

        if (!$this->canApprove($workOrder, $approver)) {
            throw new AuthorizationException('Anda tidak memiliki kewenangan untuk menyetujui QC pekerjaan ini.');
        }

        // 2. Transaksi Database dengan Row-Locking untuk Menjamin Idempotensi & Anti Double Approval
        return DB::transaction(function () use ($workOrder, $approver, $notes) {
            /** @var WorkOrder $lockedWo */
            $lockedWo = WorkOrder::where('id', $workOrder->id)->lockForUpdate()->first();
            if (!$lockedWo) {
                throw new \InvalidArgumentException("WorkOrder #{$workOrder->id} tidak ditemukan.");
            }

            // IDEMPOTENCY GUARD: Jika sudah completed dan approved_at ada, jangan proses ulang atau gandakan log
            if ($lockedWo->status === 'completed' && $lockedWo->approved_at !== null) {
                return $lockedWo;
            }

            $oldStatus = $lockedWo->status;

            // 3. Update WorkOrder Status
            $lockedWo->status = 'completed';
            $lockedWo->approved_by = $approver->id;
            $lockedWo->approved_at = now();
            if ($notes !== null && trim($notes) !== '') {
                $lockedWo->notes = trim($notes);
            }
            $lockedWo->save();

            // 4. Sinkronisasi Domain Spesifik
            // A. Instalasi PSB (CRM)
            if ($lockedWo->installation_id) {
                $inst = Installation::withoutGlobalScopes()->find($lockedWo->installation_id);
                if ($inst) {
                    $inst->update([
                        'status' => 'verified', // QC lolos -> verified (TIDAK langsung active sebelum aktivasi provisioning)
                        'completed_at' => $inst->completed_at ?? now(),
                        'completed_by' => $inst->completed_by ?: $lockedWo->assigned_to,
                    ]);

                    QualityControl::updateOrCreate(
                        ['installation_id' => $inst->id],
                        [
                            'uuid' => (string) Str::uuid(),
                            'technician_id' => $inst->technician_id ?: $lockedWo->assigned_to,
                            'status' => 'approved',
                            'notes' => $notes ?: ('QC disetujui oleh ' . $approver->name),
                            'photos' => $lockedWo->evidence_photos ?: $inst->photos,
                            'checked_at' => now(),
                            'created_by' => $approver->id,
                        ]
                    );

                    if ($inst->survey) {
                        $inst->survey->update(['status' => 'completed']);
                    }
                }
            }

            // B. Tiket Gangguan & Maintenance Terkait
            if ($lockedWo->ticket_id) {
                $ticket = Ticket::withoutGlobalScopes()->find($lockedWo->ticket_id);
                if ($ticket) {
                    $ticket->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                        'resolved_by' => $approver->id,
                        'resolution_notes' => $notes ?: ($ticket->resolution_notes ?: 'QC disetujui oleh ' . $approver->name),
                    ]);

                    // Sinkronisasi SupportMaintenance jika ada relasi
                    if ($ticket->maintenance_wo_id) {
                        SupportMaintenance::withoutGlobalScopes()
                            ->where('id', $ticket->maintenance_wo_id)
                            ->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                                'qc_verified_by' => $approver->id,
                                'qc_verified_at' => now(),
                                'qc_notes' => $notes ?: 'QC disetujui',
                            ]);
                    }

                    SupportMaintenance::withoutGlobalScopes()
                        ->where('ticket_id', $ticket->id)
                        ->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'qc_verified_by' => $approver->id,
                            'qc_verified_at' => now(),
                            'qc_notes' => $notes ?: 'QC disetujui',
                        ]);
                }
            }

            // C. Sinkronisasi jika WorkOrder bertipe maintenance langsung
            if ($lockedWo->type === 'maintenance' && $lockedWo->source_id && $lockedWo->source_type === 'maintenance') {
                SupportMaintenance::withoutGlobalScopes()
                    ->where('id', $lockedWo->source_id)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'qc_verified_by' => $approver->id,
                        'qc_verified_at' => now(),
                        'qc_notes' => $notes ?: 'QC disetujui',
                    ]);
            }

            // 5. Catat Audit Log Permanen
            AuditLog::record(
                $approver,
                'qc_approved',
                "QC SPK #{$lockedWo->id} disetujui oleh {$approver->name}",
                [
                    'work_order_id' => $lockedWo->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'completed',
                    'notes' => $notes,
                    'installation_id' => $lockedWo->installation_id,
                    'ticket_id' => $lockedWo->ticket_id,
                ],
                $lockedWo
            );

            // 6. Trigger Notifikasi Phase 3 strictly SETELAH Database Commit
            DB::afterCommit(function () use ($lockedWo, $approver, $notes) {
                try {
                    WorkOrderOperationalEvent::dispatch('approved', $lockedWo, $approver->id, ['notes' => $notes]);
                } catch (\Throwable $e) {
                    Log::warning("[UnifiedQcApprovalService] Gagal trigger WorkOrderOperationalEvent (approved): {$e->getMessage()}");
                }
            });

            return $lockedWo->fresh(['technician', 'customer', 'approver', 'ticket', 'installation']);
        });
    }

    /**
     * Tolak/Kembalikan pekerjaan lapangan untuk revisi (QC Rejected / Returned for Revision).
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function reject(WorkOrder $workOrder, User $approver, string $reason): WorkOrder
    {
        $cleanReason = trim($reason);

        // 1. Validasi Alasan Penolakan Wajib Diisi
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penolakan QC wajib diisi agar teknisi mengetahui bagian yang perlu direvisi.',
            ]);
        }

        // 2. Guard Otorisasi & Self-Rejection
        if ($workOrder->assigned_to && (int) $workOrder->assigned_to === (int) $approver->id) {
            throw new AuthorizationException('Teknisi tidak dapat menolak pekerjaannya sendiri.');
        }

        if (!$this->canReject($workOrder, $approver)) {
            throw new AuthorizationException('Anda tidak memiliki kewenangan untuk menolak QC pekerjaan ini.');
        }

        // 3. Transaksi Database dengan Row-Locking
        return DB::transaction(function () use ($workOrder, $approver, $cleanReason) {
            /** @var WorkOrder $lockedWo */
            $lockedWo = WorkOrder::where('id', $workOrder->id)->lockForUpdate()->first();
            if (!$lockedWo) {
                throw new \InvalidArgumentException("WorkOrder #{$workOrder->id} tidak ditemukan.");
            }

            $oldStatus = $lockedWo->status;

            // 4. Update WorkOrder: Kembalikan ke 'in_progress' agar teknisi dapat merevisi
            $lockedWo->status = 'in_progress';
            $lockedWo->notes = $cleanReason;
            $lockedWo->save();

            // 5. Sinkronisasi Domain Spesifik
            // A. Instalasi PSB
            if ($lockedWo->installation_id) {
                $inst = Installation::withoutGlobalScopes()->find($lockedWo->installation_id);
                if ($inst) {
                    $inst->update([
                        'status' => 'in_progress', // Dikembalikan ke teknisi
                        'notes' => "[QC Ditolak]: {$cleanReason}",
                    ]);

                    QualityControl::updateOrCreate(
                        ['installation_id' => $inst->id],
                        [
                            'uuid' => (string) Str::uuid(),
                            'technician_id' => $inst->technician_id ?: $lockedWo->assigned_to,
                            'status' => 'rejected',
                            'notes' => $cleanReason,
                            'photos' => $lockedWo->evidence_photos ?: $inst->photos,
                            'checked_at' => now(),
                            'created_by' => $approver->id,
                        ]
                    );
                }
            }

            // B. Tiket Gangguan
            if ($lockedWo->ticket_id) {
                $ticket = Ticket::withoutGlobalScopes()->find($lockedWo->ticket_id);
                if ($ticket) {
                    $ticket->update([
                        'status' => 'in_progress',
                        'description' => $ticket->description . "\n[Catatan Revisi QC]: " . $cleanReason,
                    ]);
                }
            }

            // C. SupportMaintenance
            if ($lockedWo->type === 'maintenance' && $lockedWo->source_id && $lockedWo->source_type === 'maintenance') {
                SupportMaintenance::withoutGlobalScopes()
                    ->where('id', $lockedWo->source_id)
                    ->update([
                        'status' => 'in_progress',
                        'qc_notes' => "[QC Ditolak]: {$cleanReason}",
                    ]);
            }

            // 6. Catat Audit Log Permanen
            AuditLog::record(
                $approver,
                'qc_rejected',
                "QC SPK #{$lockedWo->id} ditolak oleh {$approver->name}: {$cleanReason}",
                [
                    'work_order_id' => $lockedWo->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'in_progress',
                    'reason' => $cleanReason,
                    'installation_id' => $lockedWo->installation_id,
                    'ticket_id' => $lockedWo->ticket_id,
                ],
                $lockedWo
            );

            // 7. Trigger Notifikasi Phase 3 strictly SETELAH Database Commit
            DB::afterCommit(function () use ($lockedWo, $approver, $cleanReason) {
                try {
                    WorkOrderOperationalEvent::dispatch('rejected', $lockedWo, $approver->id, ['reason' => $cleanReason]);
                } catch (\Throwable $e) {
                    Log::warning("[UnifiedQcApprovalService] Gagal trigger WorkOrderOperationalEvent (rejected): {$e->getMessage()}");
                }
            });

            return $lockedWo->fresh(['technician', 'customer', 'approver', 'ticket', 'installation']);
        });
    }

    /**
     * Dapatkan daftar antrean pekerjaan yang menunggu persetujuan QC.
     */
    public function getPendingQcQuery(?User $user = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = WorkOrder::query()
            ->with([
                'customer',
                'technician',
                'branch',
                'reseller',
                'ticket',
                'installation.survey.prospect',
                'installation.onu',
                'checklists',
            ])
            ->where('status', 'pending_approval');

        if ($user) {
            // Filter Cabang jika pengguna terikat cabang tertentu
            if ($user->branch_id && !$user->isSuperAdmin()) {
                $query->where('branch_id', $user->branch_id);
            }

            // Filter Reseller jika pengguna adalah Reseller
            if ($user->isReseller()) {
                $resellerId = $user->getEffectiveResellerId();
                if ($resellerId) {
                    $query->where('reseller_id', $resellerId);
                }
            }
        }

        return $query->latest('completed_at')->latest('updated_at');
    }
}
