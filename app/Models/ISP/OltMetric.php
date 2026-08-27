<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OltMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'olt_id',
        'pon_port',
        'metric_key',
        'metric_value',
        'unit',
        'measured_at',
    ];

    protected $casts = [
        'olt_id' => 'integer',
        'pon_port' => 'integer',
        'metric_value' => 'float',
        'measured_at' => 'datetime',
    ];

    protected $indexes = [
        'olt_metrics_olt_measured_idx' => ['olt_id', 'measured_at'],
        'olt_metrics_key_idx' => ['metric_key', 'measured_at'],
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }
}
