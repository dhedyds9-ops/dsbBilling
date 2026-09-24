<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnuConfigurationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'onu_id',
        'customer_service_id',
        'status',
        'type',
        'payload',
        'before_state',
        'after_state',
        'error_message',
        'requested_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'before_state' => 'array',
        'after_state' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function customerService()
    {
        return $this->belongsTo(\App\Models\Customer\CustomerService::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }
}
