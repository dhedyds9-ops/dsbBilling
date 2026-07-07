<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnuPort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'onu_id',
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

    public function onu()
    {
        return $this->belongsTo(Onu::class);
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
