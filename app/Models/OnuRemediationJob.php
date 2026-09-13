<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OnuRemediationJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'onu_id', 'customer_service_id', 'type', 'status', 'target_capability',
        'acs_configuration_profile_id', 'onu_unlock_profile_id', 'acs_firmware_id',
        'dry_run_results', 'validation_results', 'error_message',
        'attempts', 'max_attempts', 'requested_by', 'approved_by',
        'approved_at', 'started_at', 'completed_at', 'plan_hash', 'expires_at'
    ];

    protected $casts = [
        'dry_run_results' => 'array',
        'validation_results' => 'array',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function onu()
    {
        return $this->belongsTo(\App\Models\ISP\Onu::class);
    }

    public function customerService()
    {
        return $this->belongsTo(\App\Models\Customer\CustomerService::class);
    }
}
