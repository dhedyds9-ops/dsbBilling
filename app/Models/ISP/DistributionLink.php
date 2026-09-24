<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'odc_id',
        'code',
        'name',
        'description',
        'capacity',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
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
