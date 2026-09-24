<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PonPort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'code',
        'name',
        'port_number',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_number' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
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
