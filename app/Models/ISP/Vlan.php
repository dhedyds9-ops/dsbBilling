<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pop_id',
        'code',
        'name',
        'description',
        'vlan_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'vlan_id' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
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
