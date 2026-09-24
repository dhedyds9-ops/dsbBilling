<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BIDashboard extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_dashboards';

    protected $fillable = [
        'name',
        'description',
        'module',
        'status',
        'widgets',
        'filters',
        'variables',
        'version',
        'created_by',
        'effective_from',
        'effective_to',
        'metadata',
    ];

    protected $casts = [
        'widgets' => 'array',
        'filters' => 'array',
        'variables' => 'array',
        'metadata' => 'array',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * Relasi ke user yang membuat
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke widgets
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(BIWidget::class, 'dashboard_id');
    }

    /**
     * Scope untuk status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk published dashboards
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope untuk module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Cek apakah dashboard bisa di-share
     */
    public function canBeShared(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get widget count
     */
    public function getWidgetCountAttribute(): int
    {
        return count($this->widgets ?? []);
    }
}
