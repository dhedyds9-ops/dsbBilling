<?php

namespace App\Models\ISP;

use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\VoucherPool;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\Voucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotspotUser extends Model
{
    use \App\Traits\HasBranchScope, \App\Traits\HasResellerScope;
    use HasFactory, SoftDeletes;

    protected $table = 'hotspot_users';

    protected $fillable = [
        'uuid', 'username', 'password', 'customer_service_id', 'service_profile_id',
        'voucher_pool_id', 'router_id', 'odp_id', 'port_number', 'mac_address',
        'static_ip', 'time_limit_hours', 'quota_gb', 'status', 'billing_cycle',
        'setup_fee', 'payment_status', 'reseller_id', 'session_timeout', 'idle_timeout',
        'simultaneous_use', 'framed_pool', 'address_list', 'expires_at', 'activated_at',
        'suspended_at', 'terminated_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime', 'suspended_at' => 'datetime',
        'terminated_at' => 'datetime', 'expires_at' => 'datetime',
    ];

    public function customerService() { return $this->belongsTo(CustomerService::class); }
    public function serviceProfile()  { return $this->belongsTo(ServiceProfile::class); }
    public function voucherPool()     { return $this->belongsTo(VoucherPool::class); }
    public function router()          { return $this->belongsTo(Router::class); }
    public function createdBy()       { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy()       { return $this->belongsTo(User::class, 'updated_by'); }
    public function reseller()        { return $this->belongsTo(User::class, 'reseller_id'); }
    public function radiusAccountings() { return $this->hasMany(RadiusAccounting::class, 'hotspot_user_id'); }
    public function vouchers()        { return $this->hasMany(Voucher::class); }

    public function customer()
    {
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class, CustomerService::class,
            'id', 'id', 'customer_service_id', 'customer_id'
        )->select('customers.*');
    }

    public function getRealCustomerAttribute()
    {
        return $this->customerService?->customer ?? $this->customer;
    }

    public function getOnuDeviceAttribute(): ?array
    {
        $cs = $this->customerService;
        if ($cs) {
            if ($cs->onu && !empty(trim((string)$cs->onu->serial_number))) {
                return [
                    'sn' => trim((string)$cs->onu->serial_number),
                    'acs_device_id' => $cs->acsDevice?->id ?? $cs->onu->acsDevice?->id,
                    'onu' => $cs->onu,
                ];
            }
            if ($cs->acsDevice && !empty(trim((string)$cs->acsDevice->serial_number))) {
                return [
                    'sn' => trim((string)$cs->acsDevice->serial_number),
                    'acs_device_id' => $cs->acsDevice->id,
                    'onu' => $cs->onu,
                ];
            }
        }

        $cust = $this->real_customer;
        if ($cust && $cust->onu_device) {
            return $cust->onu_device;
        }

        return null;
    }

    public function subscription()
    {
        return $this->hasOneThrough(
            Subscription::class, CustomerService::class,
            'id', 'customer_service_id', 'customer_service_id', 'id'
        );
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->reseller_id) && !empty($model->customer_service_id)) {
                $cs = \App\Models\Customer\CustomerService::withoutGlobalScopes()->find($model->customer_service_id);
                $model->reseller_id = $cs?->reseller_id ?? $cs?->customer?->reseller_id;
            }
        });
    }

    public function getIsOnlineAttribute()
    {
        $rawUname = trim((string)$this->username);
        if ($rawUname === '') {
            return false;
        }

        return \App\Models\ISP\HotspotActiveSession::where('user', 'like', $rawUname)->exists()
            || $this->radiusAccountings()->whereNull('acct_stop_time')->exists()
            || \App\Models\ISP\RadiusAccounting::where('username', 'like', $rawUname)->whereNull('acct_stop_time')->exists();
    }

    public function activeSession()
    {
        return $this->hasOne(\App\Models\ISP\HotspotActiveSession::class, 'user', 'username')->latestOfMany('session_started_at');
    }

    public function latestAccounting()
    {
        return $this->hasOne(\App\Models\ISP\RadiusAccounting::class, 'username', 'username')->latestOfMany('acct_start_time');
    }
}

