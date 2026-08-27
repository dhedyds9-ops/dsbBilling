<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'odc_id',
        'olt_id',
        'pon_port_id',
        'splitter_id',
        'parent_odp_id',
        'code',
        'name',
        'description',
        'address',
        'province',
        'regency',
        'district',
        'village',
        'postal_code',
        'latitude',
        'longitude',
        'split_ratio',
        'port_count',
        'used_port_count',
        'reserved_port_count',
        'status',
        'installation_date',
        'last_maintenance_at',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'split_ratio' => 'integer',
        'port_count' => 'integer',
        'used_port_count' => 'integer',
        'reserved_port_count' => 'integer',
        'installation_date' => 'date',
        'last_maintenance_at' => 'datetime',
        'attributes' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHasGps($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeNear($query, float $lat, float $lng, int $radiusKm = 5)
    {
        $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude))
            * cos(radians(longitude) - radians($lng))
            + sin(radians($lat)) * sin(radians(latitude))))";
        return $query->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", [$radiusKm])
            ->orderBy('distance');
    }

    public function odc()
    {
        return $this->belongsTo(Odc::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function ponPort()
    {
        return $this->belongsTo(PonPort::class);
    }

    public function splitter()
    {
        return $this->belongsTo(Splitter::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_odp_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_odp_id');
    }

    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function distributionBoxes()
    {
        return $this->hasMany(DistributionBox::class);
    }

    public function customerServices()
    {
        return $this->hasManyThrough(\App\Models\Customer\CustomerService::class, Onu::class);
    }

    public function customers()
    {
        return $this->hasManyThrough(\App\Models\CRM\Customer::class, \App\Models\Customer\CustomerService::class, 'onu_id', 'id', 'id', 'customer_id')
            ->whereHas('onu', fn ($q) => $q->where('odp_id', $this->id));
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function occupancyPercent(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $total = max(1, (int)($this->port_count ?? 16));
                $used = (int)($this->used_port_count ?? 0);
                return round(min(100, ($used / $total) * 100), 1);
            }
        );
    }

    public function availablePortCount(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $total = (int)($this->port_count ?? 16);
                $used = (int)($this->used_port_count ?? 0);
                $reserved = (int)($this->reserved_port_count ?? 0);
                return max(0, $total - $used - $reserved);
            }
        );
    }

    public function gpsAvailable(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->latitude !== null && $this->longitude !== null
        );
    }
}
