<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Router extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    const STATUS_ONLINE = 'online';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'critical';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'pop_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'ip_address',
        'username',
        'password',
        'radius_secret',
        'status',
        'api_port',
        'use_ssl',
        'timeout',
        'routeros_version',
        'hostname',
        'api_user',
        'api_password',
        'api_ssl_port',
        'coa_port',
        'nas_identifier',
        'is_active',
        'vpn_ip',
        'last_seen_at',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'use_ssl' => 'boolean',
        'has_config_drift' => 'boolean',
        'timeout' => 'integer',
        'api_port' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DISABLED]);
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function networkInterfaces()
    {
        return $this->morphMany(NetworkInterface::class, 'device');
    }

    public function powerSupplies()
    {
        return $this->morphMany(PowerSupply::class, 'device');
    }

    public function ups()
    {
        return $this->morphMany(Ups::class, 'device');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
    
    public function monitoringLogs()
    {
        return $this->hasMany(RouterMonitoringLog::class);
    }
    
    public function latestMonitoringLog()
    {
        return $this->hasOne(RouterMonitoringLog::class)->latestOfMany();
    }
    
    public function pppActiveSessions()
    {
        return $this->hasMany(PppActiveSession::class);
    }
    
    public function hotspotActiveSessions()
    {
        return $this->hasMany(HotspotActiveSession::class);
    }
    
    public function queueMonitoringLogs()
    {
        return $this->hasMany(QueueMonitoringLog::class);
    }

    public function getOperationalStatusAttribute(): string
    {
        if ($this->status === self::STATUS_DISABLED) {
            return self::STATUS_DISABLED;
        }

        if ($this->status === self::STATUS_MAINTENANCE) {
            return self::STATUS_MAINTENANCE;
        }

        // Check latest monitoring data
        $latestLog = $this->latestMonitoringLog()->first();

        if (!$latestLog || !$latestLog->is_online) {
            return self::STATUS_UNKNOWN;
        }

        $cachedData = \App\Services\ISP\MonitoringService::getCachedData($this);
        $healthScore = $cachedData['health_score'] ?? 100;

        if ($healthScore < 70) {
            return self::STATUS_CRITICAL;
        }

        if ($healthScore < 85) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_ONLINE;
    }
}

