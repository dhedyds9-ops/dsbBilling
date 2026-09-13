<?php

namespace App\Models\ISP;

use App\Livewire\Gis\GisMap;
use App\Services\Adapters\Provisioning\Contracts\OltDriverInterface;
use App\Services\Adapters\Provisioning\OltRegistry;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->ip_address)) {
                $model->ip_address = trim($model->ip_address);
            }
            if (!empty($model->host)) {
                $model->host = trim($model->host);
            }
            if (!empty($model->serial_number)) {
                $model->serial_number = strtoupper(trim($model->serial_number));
            }
            if (!empty($model->code)) {
                $model->code = trim($model->code);
            }
            if (!empty($model->name)) {
                $model->name = trim($model->name);
            }
            if (!empty($model->snmp_version)) {
                $model->snmp_version = preg_replace('/^v/i', '', trim($model->snmp_version));
            }
            if (!empty($model->username)) {
                $model->username = trim($model->username);
            }
            if (!empty($model->latitude)) {
                $model->latitude = trim($model->latitude);
            }
            if (!empty($model->longitude)) {
                $model->longitude = trim($model->longitude);
            }
            if (!empty($model->address)) {
                $model->address = trim($model->address);
            }
        });

        static::saved(function () {
            GisMap::flushCache();
        });

        static::deleted(function () {
            GisMap::flushCache();
        });

        static::restored(function () {
            GisMap::flushCache();
        });
    }

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
        'enable_secret',
        'port_count',
        'pon_port_count',
        'active_port_count',
        'onu_capacity',
        'onu_active_count',
        'latitude',
        'longitude',
        'address',
        'host',
        'snmp_version',
        'snmp_port',
        'snmp_community_read',
        'snmp_community_write',
        'cli_port',
        'cli_mode',
        'status',
        'last_polled_at',
        'uptime_text',
        'temperature',
        'firmware_version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'pon_port_count' => 'integer',
        'active_port_count' => 'integer',
        'onu_capacity' => 'integer',
        'onu_active_count' => 'integer',
        'snmp_port' => 'integer',
        'cli_port' => 'integer',
        'temperature' => 'float',
        'last_polled_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'enable_secret',
        'snmp_community_write',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeReachable($query)
    {
        return $query->where('status', 'active')->whereNotNull('ip_address');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function odcs()
    {
        return $this->hasMany(Odc::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function networkInterfaces()
    {
        return $this->morphMany(NetworkInterface::class, 'device');
    }

    public function ponPorts()
    {
        return $this->hasMany(PonPort::class);
    }

    public function distributionLinks()
    {
        return $this->hasMany(DistributionLink::class);
    }

    public function powerSupplies()
    {
        return $this->morphMany(PowerSupply::class, 'device');
    }

    public function ups()
    {
        return $this->morphMany(Ups::class, 'device');
    }

    public function oltMetrics()
    {
        return $this->hasMany(OltMetric::class);
    }

    public function onuSignals()
    {
        return $this->hasManyThrough(OnuSignal::class, Onu::class);
    }

    public function customerServices()
    {
        return $this->hasManyThrough(\App\Models\Customer\CustomerService::class, Onu::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function driver(): OltDriverInterface
    {
        return app(OltRegistry::class)->forOlt($this);
    }

    protected function occupancyPercent(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $cap = max(1, (int)$this->onu_capacity ?? ($this->pon_port_count * 64));
                return round(min(100, (($this->onu_active_count ?? 0) / $cap) * 100), 1);
            }
        );
    }

    public function isOnline(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                if (!$this->last_polled_at) {
                    return false;
                }
                return $this->last_polled_at->diffInMinutes(now()) <= 15;
            }
        );
    }
}

