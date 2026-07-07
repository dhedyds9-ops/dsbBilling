<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NasDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nas_devices';

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
        'nas_type',
        'status',
        'api_port',
        'use_ssl',
        'timeout',
        'routeros_version',
        'last_seen_at',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'use_ssl' => 'boolean',
        'timeout' => 'integer',
        'api_port' => 'integer',
        'last_seen_at' => 'datetime',
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

    public function networkInterfaces()
    {
        return $this->morphMany(NetworkInterface::class, 'device');
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
