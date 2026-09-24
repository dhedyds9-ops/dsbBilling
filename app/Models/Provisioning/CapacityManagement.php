<?php

namespace App\Models\Provisioning;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapacityManagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'resource_type',
        'resource_id',
        'total_capacity',
        'used_capacity',
        'available_capacity',
        'threshold_warning',
        'threshold_critical',
        'last_checked_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function resource()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
