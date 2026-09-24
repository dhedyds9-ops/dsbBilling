<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tower extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'province',
        'city',
        'district',
        'village',
        'latitude',
        'longitude',
        'height',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'height' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function pops()
    {
        return $this->hasMany(Pop::class);
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class);
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
