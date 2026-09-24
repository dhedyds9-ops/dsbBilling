<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIKPI extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bi_kpis';

    protected $fillable = [
        'type',
        'name',
        'description',
        'module',
        'granularity',
        'current_value',
        'previous_period_value',
        'same_period_last_year_value',
        'thresholds',
        'targets',
        'breakdown',
        'status',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'current_value' => 'array',
        'previous_period_value' => 'array',
        'same_period_last_year_value' => 'array',
        'thresholds' => 'array',
        'targets' => 'array',
        'breakdown' => 'array',
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
     * Scope untuk granularity
     */
    public function scopeByGranularity($query, string $granularity)
    {
        return $query->where('granularity', $granularity);
    }

    /**
     * Scope untuk active KPIs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk revenue KPIs
     */
    public function scopeRevenue($query)
    {
        return $query->whereIn('type', ['revenue', 'profit', 'arpu', 'mrr', 'arr']);
    }

    /**
     * Scope untuk customer KPIs
     */
    public function scopeCustomer($query)
    {
        return $query->whereIn('type', [
            'customer_count', 'customer_growth', 'churn_rate',
            'active_customers', 'new_customers', 'lost_customers'
        ]);
    }

    /**
     * Scope untuk network KPIs
     */
    public function scopeNetwork($query)
    {
        return $query->whereIn('type', [
            'network_availability', 'sla_compliance', 'bandwidth_usage',
            'fiber_utilization', 'olt_capacity', 'odp_capacity'
        ]);
    }

    /**
     * Scope untuk financial KPIs
     */
    public function scopeFinancial($query)
    {
        return $query->whereIn('type', [
            'outstanding_invoice', 'collection_rate', 'average_invoice_value',
            'payment_on_time_rate', 'bad_debt_rate'
        ]);
    }

    /**
     * Get current value
     */
    public function getCurrentValue(): ?float
    {
        return $this->current_value['value'] ?? null;
    }

    /**
     * Get target value
     */
    public function getTarget(): ?float
    {
        $targets = $this->targets ?? [];
        if (empty($targets)) {
            return null;
        }

        $now = now();
        $currentTarget = null;

        foreach ($targets as $target) {
            $effectiveFrom = $target['effective_from'] ?? null;
            if ($effectiveFrom && $effectiveFrom <= $now) {
                $currentTarget = $target['target'];
            }
        }

        return $currentTarget;
    }

    /**
     * Get status
     */
    public function getStatusAttribute(): string
    {
        $value = $this->getCurrentValue();
        $target = $this->getTarget();
        $thresholds = $this->thresholds ?? [];

        if ($value === null || $target === null) {
            return 'neutral';
        }

        $achievement = $target > 0 ? ($value / $target) * 100 : 0;

        if ($achievement >= 100) {
            return 'excellent';
        } elseif ($achievement >= 80) {
            return 'good';
        } elseif ($achievement >= 60) {
            return 'warning';
        }

        return 'critical';
    }

    /**
     * Get change percentage
     */
    public function getChangePercentage(): ?float
    {
        $current = $this->getCurrentValue();
        $previous = $this->previous_period_value['value'] ?? null;

        if ($current === null || $previous === null || $previous == 0) {
            return null;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
