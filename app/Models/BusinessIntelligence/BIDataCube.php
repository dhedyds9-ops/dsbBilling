<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIDataCube extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_data_cubes';

    protected $fillable = [
        'name',
        'table',
        'description',
        'module',
        'measures',
        'dimensions',
        'filters',
        'pre_aggregations',
        'last_refreshed_data',
        'last_refreshed_at',
        'statistics',
        'status',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'measures' => 'array',
        'dimensions' => 'array',
        'filters' => 'array',
        'pre_aggregations' => 'array',
        'last_refreshed_data' => 'array',
        'last_refreshed_at' => 'datetime',
        'statistics' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relasi ke user yang membuat
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk table
     */
    public function scopeByTable($query, string $table)
    {
        return $query->where('table', $table);
    }

    /**
     * Scope untuk module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope untuk active cubes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk stale cubes
     */
    public function scopeStale($query, int $minutes = 60)
    {
        return $query->where('last_refreshed_at', '<', now()->subMinutes($minutes));
    }

    /**
     * Get measure names
     */
    public function getMeasureNames(): array
    {
        return array_map(fn($m) => $m['alias'] ?? $m['field'], $this->measures ?? []);
    }

    /**
     * Get dimension names
     */
    public function getDimensionNames(): array
    {
        return array_map(fn($d) => $d['name'], $this->dimensions ?? []);
    }

    /**
     * Get row count
     */
    public function getRowCountAttribute(): int
    {
        return $this->last_refreshed_data['row_count'] ?? 0;
    }

    /**
     * Get refresh age in minutes
     */
    public function getRefreshAgeMinutesAttribute(): ?int
    {
        if (!$this->last_refreshed_at) {
            return null;
        }
        return $this->last_refreshed_at->diffInMinutes(now());
    }

    /**
     * Cek apakah cube perlu refresh
     */
    public function needsRefresh(int $intervalMinutes = 60): bool
    {
        if (!$this->last_refreshed_at) {
            return true;
        }
        return $this->last_refreshed_at->addMinutes($intervalMinutes) < now();
    }
}
