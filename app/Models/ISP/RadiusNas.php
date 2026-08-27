<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiusNas extends Model
{
    use HasFactory;

    protected $table = 'radius_nas';

    protected $fillable = [
        'uuid',
        'nas_name',
        'nas_ip_address',
        'nas_device_id',
        'nas_secret',
        'nas_type',
        'nas_port',
        'community',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function nasDevice()
    {
        return $this->belongsTo(NasDevice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function accountings()
    {
        return $this->hasMany(RadiusAccounting::class);
    }
}
