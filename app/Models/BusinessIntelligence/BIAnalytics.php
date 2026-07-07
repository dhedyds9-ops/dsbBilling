<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIAnalytics extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_analytics';

    protected $fillable = [
        'name',
        'type',
        'module',
        'time_range',
        'metrics',
        'dimensions',
        'segments',
        'comparisons',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'time_range' => 'array',
        'metrics' => 'array',
        'dimensions' => 'array',
        'segments' => 'array',
        'comparisons' => 'array',
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
     * Scope untuk revenue analytics
     */
    public function scopeRevenue($query)
    {
        return $query->where('type', 'revenue');
    }

    /**
     * Scope untuk customer analytics
     */
    public function scopeCustomer($query)
    {
        return $query->where('type', 'customer');
    }

    /**
     * Scope untuk network analytics
     */
    public function scopeNetwork($query)
    {
        return $query->where('type', 'network');
    }

    /**
     * Get time range start date
     */
    public function getStartDateAttribute(): ?string
    {
        return $this->time_range['start_date'] ?? null;
    }

    /**
     * Get time range end date
     */
    public function getEndDateAttribute(): ?string
    {
        return $this->time_range['end_date'] ?? null;
    }
}
