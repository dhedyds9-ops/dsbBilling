<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'pop_id',
        'code',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
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

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function odps()
    {
        return $this->hasMany(Odp::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function fiberCablesAsStart()
    {
        return $this->hasMany(FiberCable::class, 'start_odc_id');
    }

    public function fiberCablesAsEnd()
    {
        return $this->hasMany(FiberCable::class, 'end_odc_id');
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
