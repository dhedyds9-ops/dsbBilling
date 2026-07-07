<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_reports';

    protected $fillable = [
        'name',
        'description',
        'type',
        'module',
        'status',
        'sections',
        'filters',
        'parameters',
        'generated_file_path',
        'generated_at',
        'execution_time',
        'schedule',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'sections' => 'array',
        'filters' => 'array',
        'parameters' => 'array',
        'schedule' => 'array',
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'execution_time' => 'integer',
    ];

    /**
     * Relasi ke user yang membuat
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope untuk ready reports
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Scope untuk scheduled reports
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Cek apakah report bisa di-download
     */
    public function isDownloadable(): bool
    {
        return $this->status === 'ready' && $this->generated_file_path !== null;
    }

    /**
     * Get schedule next run
     */
    public function getNextRunAttribute(): ?string
    {
        return $this->schedule['next_run'] ?? null;
    }

    /**
     * Get recipients
     */
    public function getRecipients(): array
    {
        return $this->schedule['recipients'] ?? [];
    }
}
