<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_device_tasks';

    protected $fillable = [
        'uuid',
        'acs_device_id',
        'type',
        'parameters',
        'status',
        'started_at',
        'completed_at',
        'failed_at',
        'result',
        'error_message',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ACSDevice::class, 'acs_device_id');
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
