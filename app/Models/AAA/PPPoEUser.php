<?php

namespace App\Models\AAA;

use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\Provisioning\IpAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PPPoEUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pppoe_users';

    protected $fillable = [
        'uuid',
        'username',
        'password',
        'customer_service_id',
        'service_profile_id',
        'ip_allocation_id',
        'status',
        'activated_at',
        'suspended_at',
        'terminated_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function serviceProfile()
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function ipAllocation()
    {
        return $this->belongsTo(IpAllocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function radiusAccountings()
    {
        return $this->hasMany(RadiusAccounting::class);
    }
}
