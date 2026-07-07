<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberCore extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fiber_cable_id',
        'code',
        'name',
        'core_number',
        'color',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'core_number' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function fiberCable()
    {
        return $this->belongsTo(FiberCable::class);
    }

    public function fiberSegments()
    {
        return $this->hasMany(FiberSegment::class);
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
