<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnuSignal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'onu_id',
        'olt_id',
        'pon_port',
        'rx_power_dbm',
        'tx_power_dbm',
        'snr_db',
        'biterature_db',
        'temperature',
        'laser_bias_current',
        'voltage_v',
        'status',
        'measured_at',
    ];

    protected $casts = [
        'onu_id' => 'integer',
        'olt_id' => 'integer',
        'pon_port' => 'integer',
        'rx_power_dbm' => 'float',
        'tx_power_dbm' => 'float',
        'snr_db' => 'float',
        'temperature' => 'float',
        'laser_bias_current' => 'float',
        'voltage_v' => 'float',
        'measured_at' => 'datetime',
    ];

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }
}
