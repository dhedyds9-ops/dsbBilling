<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    use HasFactory, SoftDeletes;

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
        'port_count',
        'active_port_count',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'active_port_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
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

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
