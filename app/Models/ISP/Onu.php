<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'pon_port_id',
        'splitter_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'mac_address',
        'pon_port',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'pon_port' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function onuPorts()
    {
        return $this->hasMany(OnuPort::class);
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
}
