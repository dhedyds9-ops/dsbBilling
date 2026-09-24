<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pop_id',
        'tower_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'ip_address',
        'username',
        'password',
        'ssid',
        'status',
        'created_by',
        'updated_by',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class);
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
