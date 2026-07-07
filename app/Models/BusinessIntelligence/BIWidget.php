<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIWidget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_widgets';

    protected $fillable = [
        'dashboard_id',
        'name',
        'type',
        'data_source',
        'dimensions',
        'measures',
        'filters',
        'chart_config',
        'formatting',
        'refresh_interval',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'data_source' => 'array',
        'dimensions' => 'array',
        'measures' => 'array',
        'filters' => 'array',
        'chart_config' => 'array',
        'formatting' => 'array',
        'metadata' => 'array',
        'refresh_interval' => 'integer',
    ];

    /**
     * Relasi ke dashboard
     */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(BIDashboard::class, 'dashboard_id');
    }

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
     * Scope untuk chart types
     */
    public function scopeCharts($query)
    {
        return $query->whereIn('type', [
            'line_chart', 'bar_chart', 'pie_chart', 'doughnut_chart', 'area_chart'
        ]);
    }

    /**
     * Scope untuk KPI cards
     */
    public function scopeKPICards($query)
    {
        return $query->whereIn('type', ['number', 'gauge', 'kpi_card']);
    }

    /**
     * Get refresh interval dalam menit
     */
    public function getRefreshIntervalMinutesAttribute(): float
    {
        return $this->refresh_interval / 60;
    }
}
