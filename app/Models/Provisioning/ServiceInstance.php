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
        return $this->belongsTo(CustomerService::class)->withTrashed();
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

    public function customer()
    {
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class,
            CustomerService::class,
            'id',
            'id',
            'customer_service_id',
            'customer_id'
        )->withTrashedParents();
    }

    public function getBranchIdAttribute(): ?int
    {
        return $this->customerService?->branch_id;
    }

    public function getResellerIdAttribute(): ?int
    {
        return $this->customerService?->reseller_id;
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
