<?php

namespace App\Models\Provisioning;

use App\Models\Customer\CustomerService;
use App\Models\ServiceCatalog\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_service_id',
        'service_id',
        'status',
        'provisioned_at',
        'activated_at',
        'suspended_at',
        'terminated_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function provisionPipeline()
    {
        return $this->hasOne(ProvisionPipeline::class);
    }

    public function resourceAssignments()
    {
        return $this->hasMany(ResourceAssignment::class);
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
