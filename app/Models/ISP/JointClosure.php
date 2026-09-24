<?php

namespace App\Models\ISP;

use App\Livewire\Gis\GisMap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JointClosure extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
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
        'vendor_id',
        'odc_id',
        'code',
        'name',
        'description',
        'type',
        'port_count',
        'address',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function odc()
    {
        return $this->belongsTo(Odc::class);
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
