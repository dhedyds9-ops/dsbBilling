<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnuRemediationJobLog extends Model
{
    protected $fillable = [
        'onu_remediation_job_id', 'step_index', 'action', 'actor_id', 
        'profile_id', 'status', 'evidence_before', 'evidence_after', 
        'result', 'error_message', 'started_at', 'completed_at', 'attempt'
    ];

    protected $casts = [
        'evidence_before' => 'array',
        'evidence_after' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(OnuRemediationJob::class, 'onu_remediation_job_id');
    }
}
