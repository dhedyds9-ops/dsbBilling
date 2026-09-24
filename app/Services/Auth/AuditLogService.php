<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditLogService — Centralized service untuk audit trail perubahan
 * role, permission, dan konfigurasi sistem.
 *
 * PRINSIP SSOT:
 *  - Semua perubahan role/permission HARUS dicatat lewat service ini,
 *    TIDAK langsung mutate pivot table di controller/Livewire.
 *  - Audit log TIDAK BOLEH dihapus (immutable).
 *  - Informasi yang dicatat: actor (user login), target (model),
 *    before/after values, event (action), IP, user agent, note.
 */
class AuditLogService
{
    // =============================================
    // EVENT NAMES (immutable constants)
    // =============================================
    public const EVENT_ROLE_ATTACHED   = 'role.attached';
    public const EVENT_ROLE_DETACHED   = 'role.detached';
    public const EVENT_ROLE_SYNCED     = 'role.synced';
    public const EVENT_PERMISSION_SYNCED = 'permission.synced';
    public const EVENT_PERMISSION_ATTACHED = 'permission.attached';
    public const EVENT_PERMISSION_DETACHED = 'permission.detached';
    public const EVENT_SETTINGS_UPDATED = 'settings.updated';
    public const EVENT_CONFIG_CHANGED  = 'config.changed';
    public const EVENT_USER_CREATED    = 'user.created';
    public const EVENT_USER_UPDATED    = 'user.updated';

    // =============================================
    // CORE WRITE LOG
    // =============================================

    /**
     * Tulis audit log generik.
     */
    public function log(
        string $event,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $notes = null,
    ): AuditLog {
        $log = new AuditLog();
        $log->event = $event;
        $log->old_values = $oldValues;
        $log->new_values = $newValues;
        $log->ip_address = Request::ip();
        $log->user_agent = Request::userAgent();
        $log->notes = $notes;

        if (Auth::check()) {
            $log->user_id = Auth::id();
        }

        if ($auditable !== null) {
            $log->auditable_type = $auditable::class;
            $log->auditable_id = $auditable->getKey();
        }

        $log->save();

        return $log;
    }

    // =============================================
    // ROLE CHANGE AUDITS
    // =============================================

    /**
     * Audit sinkronisasi role pada user.
     * Membandingkan role lama vs baru dan mencatat perbedaannya.
     */
    public function auditRoleSync(User $targetUser, array $oldRoleIds, array $newRoleIds): void
    {
        $oldRoles = Role::whereIn('id', $oldRoleIds)->pluck('name')->toArray();
        $newRoles = Role::whereIn('id', $newRoleIds)->pluck('name')->toArray();

        sort($oldRoles);
        sort($newRoles);

        if ($oldRoles === $newRoles) {
            return; // Tidak ada perubahan, skip audit
        }

        $added = array_values(array_diff($newRoles, $oldRoles));
        $removed = array_values(array_diff($oldRoles, $newRoles));

        $notes = [];
        if (!empty($added)) {
            $notes[] = 'Added roles: ' . implode(', ', $added);
        }
        if (!empty($removed)) {
            $notes[] = 'Removed roles: ' . implode(', ', $removed);
        }

        $this->log(
            event: self::EVENT_ROLE_SYNCED,
            auditable: $targetUser,
            oldValues: ['roles' => $oldRoles],
            newValues: ['roles' => $newRoles],
            notes: implode(' | ', $notes) ?: 'Role assignment updated.',
        );
    }

    // =============================================
    // PERMISSION CHANGE AUDITS
    // =============================================

    /**
     * Audit sinkronisasi direct permission pada user.
     */
    public function auditUserPermissionSync(User $targetUser, array $oldPermissionIds, array $newPermissionIds): void
    {
        $oldPerms = Permission::whereIn('id', $oldPermissionIds)->pluck('name')->toArray();
        $newPerms = Permission::whereIn('id', $newPermissionIds)->pluck('name')->toArray();

        sort($oldPerms);
        sort($newPerms);

        if ($oldPerms === $newPerms) {
            return;
        }

        $added = array_values(array_diff($newPerms, $oldPerms));
        $removed = array_values(array_diff($oldPerms, $newPerms));

        $notes = [];
        if (!empty($added)) {
            $notes[] = 'Granted: ' . implode(', ', $added);
        }
        if (!empty($removed)) {
            $notes[] = 'Revoked: ' . implode(', ', $removed);
        }

        // SECURITY: Jika ada perubahan NOC.manage, role.manage, settings.update → catat elevated access
        $elevated = ['noc.onu.manage', 'noc.alarms.manage', 'role.manage', 'settings.update'];
        $escalation = array_intersect($added, $elevated);
        if (!empty($escalation)) {
            $notes[] = '⚠ ELEVATED ACCESS GRANTED: ' . implode(', ', $escalation);
        }

        $this->log(
            event: self::EVENT_PERMISSION_SYNCED,
            auditable: $targetUser,
            oldValues: ['permissions' => $oldPerms],
            newValues: ['permissions' => $newPerms],
            notes: implode(' | ', $notes) ?: 'Direct permissions updated.',
        );
    }

    /**
     * Audit sinkronisasi permission pada Role (role permission matrix).
     */
    public function auditRolePermissionSync(Role $role, array $oldPermissionIds, array $newPermissionIds): void
    {
        $oldPerms = Permission::whereIn('id', $oldPermissionIds)->pluck('name')->toArray();
        $newPerms = Permission::whereIn('id', $newPermissionIds)->pluck('name')->toArray();

        sort($oldPerms);
        sort($newPerms);

        if ($oldPerms === $newPerms) {
            return;
        }

        $added = array_values(array_diff($newPerms, $oldPerms));
        $removed = array_values(array_diff($oldPerms, $newPerms));

        $notes = ["Role: {$role->name}"];
        if (!empty($added)) {
            $notes[] = 'Granted: ' . implode(', ', $added);
        }
        if (!empty($removed)) {
            $notes[] = 'Revoked: ' . implode(', ', $removed);
        }

        $this->log(
            event: self::EVENT_PERMISSION_SYNCED,
            auditable: $role,
            oldValues: ['permissions' => $oldPerms],
            newValues: ['permissions' => $newPerms],
            notes: implode(' | ', $notes),
        );
    }

    // =============================================
    // SETTINGS / CONFIG AUDITS
    // =============================================

    /**
     * Audit perubahan konfigurasi sistem (Pengaturan, Setting model, dll).
     */
    public function auditSettingsUpdate(Model $configModel, array $oldValues, array $newValues, ?string $label = null): void
    {
        $diffOld = [];
        $diffNew = [];
        foreach ($newValues as $key => $value) {
            $ov = $oldValues[$key] ?? null;
            if ($ov != $value) {
                $diffOld[$key] = $ov;
                $diffNew[$key] = $value;
            }
        }

        if (empty($diffOld) && empty($diffNew)) {
            return;
        }

        $this->log(
            event: self::EVENT_SETTINGS_UPDATED,
            auditable: $configModel,
            oldValues: $diffOld,
            newValues: $diffNew,
            notes: $label ?? 'Configuration updated.',
        );
    }

    // =============================================
    // USER CREATE / UPDATE AUDITS
    // =============================================

    public function auditUserCreated(User $user, array $createdValues): void
    {
        $this->log(
            event: self::EVENT_USER_CREATED,
            auditable: $user,
            oldValues: [],
            newValues: $createdValues,
            notes: 'User account created.',
        );
    }

    public function auditUserUpdated(User $user, array $oldValues, array $newValues): void
    {
        $diffOld = [];
        $diffNew = [];
        $sensitive = ['password', 'remember_token'];
        foreach ($newValues as $key => $value) {
            if (in_array($key, $sensitive, true)) {
                continue; // Jangan simpan password di audit log
            }
            $ov = $oldValues[$key] ?? null;
            if ((string) $ov !== (string) $value) {
                $diffOld[$key] = $ov;
                $diffNew[$key] = $value;
            }
        }

        if (empty($diffOld) && empty($diffNew)) {
            return;
        }

        $this->log(
            event: self::EVENT_USER_UPDATED,
            auditable: $user,
            oldValues: $diffOld,
            newValues: $diffNew,
            notes: 'User profile updated.',
        );
    }

    // =============================================
    // QUERY HELPERS (READ)
    // =============================================

    /**
     * Ambil log audit untuk user tertentu.
     */
    public function getLogsForUser(User $user, int $limit = 50)
    {
        return AuditLog::where(function ($q) use ($user) {
                $q->where('auditable_type', User::class)
                  ->where('auditable_id', $user->id);
            })
            ->orWhere('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil semua event audit terkait permission/role (security audit).
     */
    public function getSecurityLogs(int $limit = 100)
    {
        return AuditLog::whereIn('event', [
                self::EVENT_ROLE_ATTACHED,
                self::EVENT_ROLE_DETACHED,
                self::EVENT_ROLE_SYNCED,
                self::EVENT_PERMISSION_SYNCED,
                self::EVENT_PERMISSION_ATTACHED,
                self::EVENT_PERMISSION_DETACHED,
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
