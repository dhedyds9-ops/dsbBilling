<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'odc_id',
        'code',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'port_count',
        'active_port_count',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'active_port_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function odc()
    {
        return $this->belongsTo(Odc::class);
    }

    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }

    public function distributionBoxes()
    {
        return $this->hasMany(DistributionBox::class);
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
