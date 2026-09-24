<?php

namespace App\Models\BusinessIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BIForecast extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bi_forecasts';

    protected $fillable = [
        'metric_name',
        'model',
        'granularity',
        'module',
        'horizon_periods',
        'confidence_level',
        'historical_data',
        'result',
        'model_parameters',
        'validation_metrics',
        'prediction_period',
        'status',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'historical_data' => 'array',
        'result' => 'array',
        'model_parameters' => 'array',
        'validation_metrics' => 'array',
        'prediction_period' => 'array',
        'metadata' => 'array',
        'horizon_periods' => 'integer',
        'confidence_level' => 'float',
    ];

    /**
     * Relasi ke user yang membuat
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk metric name
     */
    public function scopeByMetricName($query, string $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope untuk model
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope untuk module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope untuk active forecasts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get predictions
     */
    public function getPredictions(): array
    {
        return $this->result['predictions'] ?? [];
    }

    /**
     * Get confidence intervals
     */
    public function getConfidenceIntervals(): array
    {
        return $this->result['confidence_intervals'] ?? [];
    }

    /**
     * Get accuracy
     */
    public function getAccuracy(): float
    {
        return $this->validation_metrics['accuracy'] ?? 0;
    }

    /**
     * Get model label
     */
    public function getModelLabelAttribute(): string
    {
        return match($this->model) {
            'linear_regression' => 'Linear Regression',
            'exponential_smoothing' => 'Exponential Smoothing',
            'arima' => 'ARIMA',
            'moving_average' => 'Moving Average',
            'polynomial' => 'Polynomial',
            'prophet' => 'Prophet',
            default => $this->model,
        };
    }

    /**
     * Get average prediction
     */
    public function getAveragePrediction(): float
    {
        $predictions = $this->getPredictions();
        if (empty($predictions)) {
            return 0;
        }
        return array_sum($predictions) / count($predictions);
    }
}
