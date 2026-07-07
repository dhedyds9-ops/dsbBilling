<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tower_id',
        'code',
        'name',
        'description',
        'address',
        'province',
        'city',
        'district',
        'village',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class);
    }

    public function olts()
    {
        return $this->hasMany(Olt::class);
    }

    public function odcs()
    {
        return $this->hasMany(Odc::class);
    }

    public function routers()
    {
        return $this->hasMany(Router::class);
    }

    public function switches()
    {
        return $this->hasMany(Switcher::class);
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class);
    }

    public function nasDevices()
    {
        return $this->hasMany(NasDevice::class);
    }

    public function ipPools()
    {
        return $this->hasMany(IpPool::class);
    }

    public function vlans()
    {
        return $this->hasMany(Vlan::class);
    }

    public function dnsServers()
    {
        return $this->hasMany(DnsServer::class);
    }

    public function radiusServers()
    {
        return $this->hasMany(RadiusServer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }

    public function backboneLinksAsStart()
    {
        return $this->hasMany(BackboneLink::class, 'start_pop_id');
    }

    public function backboneLinksAsEnd()
    {
        return $this->hasMany(BackboneLink::class, 'end_pop_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
