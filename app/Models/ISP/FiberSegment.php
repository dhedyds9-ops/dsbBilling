<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fiber_core_id',
        'start_device_id',
        'start_device_type',
        'end_device_id',
        'end_device_type',
        'description',
        'length',
        'loss',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'loss' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function fiberCore()
    {
        return $this->belongsTo(FiberCore::class);
    }

    public function startDevice()
    {
        return $this->morphTo();
    }

    public function endDevice()
    {
        return $this->morphTo();
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
