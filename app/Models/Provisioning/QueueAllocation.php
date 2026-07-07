<?php

namespace App\Models\Provisioning;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'service_instance_id',
        'queue_name',
        'queue_id',
        'priority',
        'download_limit',
        'upload_limit',
        'burst_limit',
        'status',
        'allocated_at',
        'deallocated_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'deallocated_at' => 'datetime',
    ];

    public function serviceInstance()
    {
        return $this->belongsTo(ServiceInstance::class);
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
