<?php

namespace App\Models\Provisioning;

use App\Models\ISP\Onu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'service_instance_id',
        'onu_id',
        'serial_number',
        'mac_address',
        'device_type',
        'status',
        'assigned_at',
        'unassigned_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function serviceInstance()
    {
        return $this->belongsTo(ServiceInstance::class);
    }

    public function onu()
    {
        return $this->belongsTo(Onu::class);
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
