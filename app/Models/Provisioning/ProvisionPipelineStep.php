<?php

namespace App\Models\Provisioning;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisionPipelineStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'provision_pipeline_id',
        'step_name',
        'step_type',
        'order',
        'status',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'rollback_needed',
        'rollback_completed_at',
        'payload',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'rollback_completed_at' => 'datetime',
        'payload' => 'array',
    ];

    public function pipeline()
    {
        return $this->belongsTo(ProvisionPipeline::class);
    }
}
