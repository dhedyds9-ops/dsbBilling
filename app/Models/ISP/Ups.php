<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ups extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_id',
        'device_type',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'power_rating',
        'battery_capacity',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'power_rating' => 'decimal:2',
        'battery_capacity' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function device()
    {
        return $this->morphTo();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
