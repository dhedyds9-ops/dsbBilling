<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberCable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'start_odc_id',
        'end_odc_id',
        'code',
        'name',
        'description',
        'type',
        'core_count',
        'length',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'core_count' => 'integer',
        'length' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function startOdc()
    {
        return $this->belongsTo(Odc::class, 'start_odc_id');
    }

    public function endOdc()
    {
        return $this->belongsTo(Odc::class, 'end_odc_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function fiberCores()
    {
        return $this->hasMany(FiberCore::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
