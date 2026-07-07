<?php

namespace App\Models\Provisioning;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisionPipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'service_instance_id',
        'current_step',
        'total_steps',
        'status',
        'error_message',
        'started_at',
        'completed_at',
        'failed_at',
        'rollback_steps',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'rollback_steps' => 'array',
    ];

    public function serviceInstance()
    {
        return $this->belongsTo(ServiceInstance::class);
    }

    public function steps()
    {
        return $this->hasMany(ProvisionPipelineStep::class)->orderBy('order');
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
