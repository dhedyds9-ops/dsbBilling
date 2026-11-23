<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit event safely without throwing exceptions.
     */
    public static function record($user, string $event, string $notes, array $details = [], ?Model $auditable = null): ?self
    {
        try {
            $auditableModel = $auditable ?: (is_object($user) && $user instanceof Model ? $user : null);
            return static::create([
                'auditable_type' => $auditableModel ? get_class($auditableModel) : (string) ($details['auditable_type'] ?? User::class),
                'auditable_id' => $auditableModel ? $auditableModel->getKey() : (int) ($details['auditable_id'] ?? (is_object($user) ? $user->id : 1)),
                'user_id' => is_object($user) ? $user->id : (is_numeric($user) ? (int)$user : null),
                'event' => $event,
                'notes' => $notes,
                'new_values' => $details,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to record AuditLog: " . $e->getMessage());
            return null;
        }
    }
}
