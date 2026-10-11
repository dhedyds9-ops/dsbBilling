<?php

namespace Src\Domain\Voucher\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Audit log helper untuk Voucher domain.
 * Tanggung jawab tunggal: menulis AuditLog untuk aksi pada Voucher.
 */
class VoucherAuditLogger
{
    public function log($model, string $event, ?array $oldValues, ?array $newValues, ?User $user): void
    {
        try {
            $audit = [
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model?->id,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            if (function_exists('audit_log_create')) {
                audit_log_create($audit);
            } else {
                AuditLog::create($audit);
            }
        } catch (\Exception $e) {
            Log::error('Voucher audit log failed: ' . $e->getMessage(), [
                'model' => $model ? get_class($model) : null,
                'id' => $model?->id ?? null,
            ]);
        }
    }
}
