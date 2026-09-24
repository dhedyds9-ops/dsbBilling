<?php

namespace App\Models\Provisioning;

use App\Models\ISP\IpPool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'service_instance_id',
        'ip_pool_id',
        'ip_address',
        'subnet_mask',
        'gateway',
        'dns_servers',
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

    public function ipPool()
    {
        return $this->belongsTo(IpPool::class);
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
