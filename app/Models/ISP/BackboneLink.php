<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackboneLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'start_pop_id',
        'end_pop_id',
        'code',
        'name',
        'description',
        'type',
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

    public function startPop()
    {
        return $this->belongsTo(Pop::class, 'start_pop_id');
    }

    public function endPop()
    {
        return $this->belongsTo(Pop::class, 'end_pop_id');
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
