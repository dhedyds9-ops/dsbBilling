<?php

namespace App\Services\Workforce;

use App\Models\User;
use App\Models\Workforce\Attendance;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceAuthorizationService
{
    /**
     * Get the active attendance record for the given user.
     * Criteria for active attendance (Section 3 of Directive):
     * 1. Owned by the specified user (technician_id == user.id)
     * 2. Has a valid checked_in_at timestamp
     * 3. Has NO checked_out_at timestamp (checked_out_at is null)
     * 4. Status is active (not absent, permission, sick, or cancelled)
     * 5. Valid within operational shift window:
     *    - Started today OR
     *    - Started within the last 18 hours (supporting night shifts across midnight)
     */
    public static function getActiveAttendance(?User $user): ?Attendance
    {
        if (!$user) {
            return null;
        }

        $now = Carbon::now();
        $cutoff = $now->copy()->subHours(18);

        return Attendance::where('technician_id', $user->id)
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->where(function ($q) use ($cutoff) {
                $q->whereDate('date', Carbon::today())
                  ->orWhere('checked_in_at', '>=', $cutoff);
            })
            ->whereNotIn('status', ['absent', 'permission', 'sick', 'cancelled', 'checked_out'])
            ->orderBy('checked_in_at', 'desc')
            ->first();
    }

    /**
     * Check whether the user has an active, valid attendance session.
     */
    public static function hasActiveAttendance(?User $user): bool
    {
        return self::getActiveAttendance($user) !== null;
    }

    /**
     * Check if the user is exempt from mandatory attendance.
     * Pure administrators / superadmins performing high-level administrative oversight
     * who are not acting as operational field staff are exempt.
     * Reseller clients are also exempt from staff attendance.
     */
    public static function isExemptFromAttendance(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $isPureAdminOversight = $user->hasRole(['administrator', 'superadmin'])
            && !in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS'])
            && !str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
            && !str_contains(strtolower((string) ($user->job_title ?? '')), 'teknisi');

        if ($isPureAdminOversight) {
            return true;
        }

        // Customer & pure Reseller are not internal operational workforce staff
        if ($user->hasRole(['customer', 'reseller']) && !$user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service'])) {
            return true;
        }

        // Attendance-based operational gate strictly mandates attendance for field technicians
        if (!$user->isTechnician()) {
            return true;
        }

        return false;
    }

    /**
     * Evaluate if user can execute operational field/network/inventory actions.
     */
    public static function canPerformOperations(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (self::isExemptFromAttendance($user)) {
            return true;
        }

        return self::hasActiveAttendance($user);
    }

    /**
     * Authorize access to a Work Order respecting cross-branch assignment (Section 6).
     * Rule: A technician from Branch A CAN work on an SPK in Branch B if:
     * 1. They have an active attendance.
     * 2. The SPK is assigned to them (or they are manager/admin).
     */
    public static function authorizeWorkOrder(User $user, WorkOrder $workOrder): array
    {
        // 1. Mandatory Attendance Gate
        if (!self::canPerformOperations($user)) {
            return [
                'allowed' => false,
                'code' => 'ATTENDANCE_REQUIRED',
                'message' => 'Pengguna harus melakukan absensi masuk sebelum menjalankan aktivitas operasional.',
                'status' => 403,
            ];
        }

        // 2. Company / Tenant Isolation
        if ($user->company_id && $workOrder->company_id && (int)$user->company_id !== (int)$workOrder->company_id && !$user->isSuperAdmin()) {
            return [
                'allowed' => false,
                'code' => 'FORBIDDEN_COMPANY',
                'message' => 'Akses ditolak: SPK ini milik perusahaan lain.',
                'status' => 403,
            ];
        }

        $isManagerOrAdmin = $user->isSuperAdmin()
            || $user->hasRole(['manager', 'administrator', 'superadmin'])
            || in_array(strtoupper($user->job_function ?? ''), ['MANAGER', 'ADMINISTRATOR']);

        $isAssigned = $workOrder->assigned_to && (int)$workOrder->assigned_to === (int)$user->id;

        // 3. Cross-Branch Evaluation:
        // If technician is from another branch and NOT assigned, reject!
        if ($user->branch_id && $workOrder->branch_id && (int)$user->branch_id !== (int)$workOrder->branch_id) {
            if (!$isAssigned && !$isManagerOrAdmin) {
                return [
                    'allowed' => false,
                    'code' => 'CROSS_BRANCH_UNASSIGNED',
                    'message' => 'Akses ditolak: SPK ini milik cabang lain dan tidak ditugaskan kepada Anda.',
                    'status' => 403,
                ];
            }
        }

        // 4. Technician assignment check within branch
        if ($user->isTechnician() && !$isManagerOrAdmin) {
            if ($workOrder->assigned_to && !$isAssigned) {
                return [
                    'allowed' => false,
                    'code' => 'ASSIGNED_TO_OTHER',
                    'message' => 'Akses ditolak: SPK ini ditugaskan kepada teknisi lain.',
                    'status' => 403,
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Create standard audit trail text recording cross-branch context.
     */
    public static function formatAuditTrail(
        string $action,
        User $technician,
        ?int $targetBranchId,
        ?string $notes = null
    ): string {
        $timestamp = now()->format('Y-m-d H:i');
        $activeAtt = self::getActiveAttendance($technician);
        $attIdStr = $activeAtt ? "Att #{$activeAtt->id}" : "Oversight";
        
        $branchInfo = "";
        if ($targetBranchId && $technician->branch_id && (int)$technician->branch_id !== (int)$targetBranchId) {
            $branchInfo = " [CROSS-BRANCH: Tech Cabang #{$technician->branch_id} -> Target Cabang #{$targetBranchId}]";
        } elseif ($technician->branch_id) {
            $branchInfo = " [Cabang #{$technician->branch_id}]";
        }

        $line = "[{$action} {$timestamp}]{$branchInfo} Dilakukan oleh {$technician->name} ({$attIdStr}).";
        if (!empty($notes)) {
            $line .= " Catatan: {$notes}";
        }

        return $line;
    }
}
