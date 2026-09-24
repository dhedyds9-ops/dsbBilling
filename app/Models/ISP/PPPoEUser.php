<?php

namespace App\Models\ISP;

use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\RadiusAccounting;
use App\Models\Provisioning\IpAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PPPoEUser extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected $table = 'pppoe_users';

    protected $fillable = [
        'uuid', 'username', 'password', 'customer_service_id', 'service_profile_id',
        'ip_allocation_id', 'router_id', 'odp_id', 'port_number', 'mac_address',
        'static_ip', 'status', 'billing_cycle', 'setup_fee', 'payment_status',
        'reseller_id', 'session_timeout', 'idle_timeout', 'simultaneous_use',
        'framed_pool', 'address_list', 'expires_at', 'activated_at', 'suspended_at',
        'terminated_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime', 'suspended_at' => 'datetime',
        'terminated_at' => 'datetime', 'expires_at' => 'datetime',
    ];

    public function customerService() { return $this->belongsTo(CustomerService::class); }
    public function serviceProfile()  { return $this->belongsTo(ServiceProfile::class); }
    public function ipAllocation()    { return $this->belongsTo(IpAllocation::class); }
    public function router()          { return $this->belongsTo(Router::class); }
    public function odp()             { return $this->belongsTo(\App\Models\ISP\Odp::class); }
    public function createdBy()       { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy()       { return $this->belongsTo(User::class, 'updated_by'); }
    public function reseller()        { return $this->belongsTo(User::class, 'reseller_id'); }
    public function radiusAccountings() { return $this->hasMany(RadiusAccounting::class, 'pppoe_user_id'); }

    public function customer()
    {
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class, CustomerService::class,
            'id', 'id', 'customer_service_id', 'customer_id'
        );
    }

    public function subscription()
    {
        return $this->hasOneThrough(
            Subscription::class, CustomerService::class,
            'id', 'customer_service_id', 'customer_service_id', 'id'
        );
    }

    public function getIsOnlineAttribute()
    {
        return $this->radiusAccountings()->whereNull('acct_stop_time')->exists();
    }

    public function latestAccounting()
    {
        return $this->hasOne(\App\Models\ISP\RadiusAccounting::class, 'pppoe_user_id')->latestOfMany('acct_start_time');
    }
}
