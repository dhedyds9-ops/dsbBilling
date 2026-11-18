<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PonPort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'code',
        'name',
        'port_number',
        'port_index',
        'type',
        'tx_power_dbm',
        'rx_power_dbm',
        'sfp_class',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_number' => 'integer',
        'port_index' => 'integer',
        'tx_power_dbm' => 'float',
        'rx_power_dbm' => 'float',
    ];

    /**
     * Get SFP Optical Tx Power with smart fallback
     */
    public function getEffectiveTxPower(): float
    {
        if ($this->tx_power_dbm !== null && $this->tx_power_dbm > 0) {
            return (float) $this->tx_power_dbm;
        }

        return match ($this->sfp_class) {
            'Class B+' => 3.0,
            'Class C+' => 4.5,
            'Class C++' => 6.0,
            'Class D' => 7.5,
            default => 4.5,
        };
    }

    public function getPortIndexAttribute($value)
    {
        return $value ?? $this->port_number;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
