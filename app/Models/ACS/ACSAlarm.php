<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ACSAlarm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_alarms';

    protected $fillable = [
        'uuid',
        'acs_device_id',
        'severity',
        'message',
        'details',
        'status',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'details' => 'array',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ACSDevice::class, 'acs_device_id');
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
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
