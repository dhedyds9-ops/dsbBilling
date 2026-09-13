<?php

namespace App\Models\ISP;

use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProfile extends Model
{
    use \App\Traits\HasBranchScope;
    use SoftDeletes;

    protected $table = 'service_profiles';

    protected $fillable = [
        'service_profile_type_id',
        'owner_id',
        'tenant_id',
        'branch_id',
        'visibility',
        'code',
        'name',
        'description',
        'service_type', // PPPoE, Hotspot, Voucher, FTTH, Kombinasi
        'package_type', // unlimited, time_based, quota_based
        'duration_value',
        'duration_unit', // hours, days
        'quota_value',
        'quota_unit', // MB, GB
        'download_speed',
        'upload_speed',
        'burst_limit_download',
        'burst_limit_upload',
        'burst_threshold_download',
        'burst_threshold_upload',
        'burst_time_download',
        'burst_time_upload',
        'queue_type', // Simple Queue, Queue Tree, PCQ
        'priority',
        'cir_download',
        'cir_upload',
        'mir_download',
        'mir_upload',
        'fup_enabled',
        'fup_threshold',
        'fup_speed_drop_percent',
        'radius_group_name',
        'radius_session_timeout',
        'radius_idle_timeout',
        'radius_simultaneous_use',
        'radius_mac_binding',
        'radius_address_list',
        'radius_rate_limit',
        'radius_framed_pool',
        'radius_attributes', // JSON
        'account_type', // Unlimited, Limited
        'validity_days',
        'validity_hours',
        'validity_unit', // days, months, hours
        'allowed_login_days', // JSON
        'login_start_time',
        'login_end_time',
        'max_devices',
        'idle_disconnect_policy',
        'voucher_show_in_portal',
        'voucher_template',
        'voucher_prefix',
        'voucher_length',
        'voucher_print_format',
        'voucher_validity_after_activation',
        'cost_price',
        'base_price',
        'owner_price',
        'reseller_price',
        'owner_settlement_price',
        'branch_settlement_price',
        'reseller_settlement_price',
        'is_free',
        'promo_price',
        'tax_enabled',
        'tax_percentage',
        'prorata_billing',
        'billing_cycle', // Monthly, Yearly, etc
        'billing_cycle_day',
        'min_deposit',
        'auto_suspend_days',
        'auto_activate_after_payment',
        'target_hotspot_profile',
        'ppp_profile_name',
        'user_manager_profile',
        'vlan_id',
        'bridge_interface',
        'interface_name',
        'ip_pool_parent',
        'custom_dns',
        'technical_notes',
        'advanced_settings', // JSON
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'burst_limit_download' => 'integer',
        'burst_limit_upload' => 'integer',
        'burst_threshold_download' => 'integer',
        'burst_threshold_upload' => 'integer',
        'burst_time_download' => 'integer',
        'burst_time_upload' => 'integer',
        'priority' => 'integer',
        'cir_download' => 'integer',
        'cir_upload' => 'integer',
        'mir_download' => 'integer',
        'mir_upload' => 'integer',
        'fup_enabled' => 'boolean',
        'fup_threshold' => 'integer',
        'fup_speed_drop_percent' => 'integer',
        'radius_mac_binding' => 'boolean',
        'radius_attributes' => 'array',
        'allowed_login_days' => 'array',
        'voucher_show_in_portal' => 'boolean',
        'cost_price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'owner_price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
        'is_free' => 'boolean',
        'duration_value' => 'integer',
        'quota_value' => 'integer',
        'promo_price' => 'decimal:2',
        'tax_enabled' => 'boolean',
        'tax_percentage' => 'decimal:2',
        'prorata_billing' => 'boolean',
        'auto_suspend_days' => 'integer',
        'auto_activate_after_payment' => 'boolean',
        'advanced_settings' => 'array',
        'custom_dns' => 'array',
        'validity_days' => 'integer',
        'validity_hours' => 'integer',
        'max_devices' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Relationships
    public function serviceProfileType(): BelongsTo
    {
        return $this->belongsTo(ServiceProfileType::class);
    }

    public function vlan(): BelongsTo
    {
        return $this->belongsTo(Vlan::class);
    }

    public function customerServices(): HasMany
    {
        return $this->hasMany(\App\Models\Customer\CustomerService::class, 'service_profile_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Pemilik/admin yang membuat service profile ini.
     * Catatan: owner_id di service_profiles berbeda dengan di members/pppoe_users.
     * Di sini owner_id = administrator/manager yang membuat profile ini,
     * BUKAN reseller. Untuk service profile milik reseller, gunakan reseller_id.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Branch/cabang yang memiliki service profile ini.
     * branch_id merujuk ke tabel branches (business entity, bukan role).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Helper Methods
    public function getActiveCustomersCountAttribute()
    {
        return $this->customerServices()->where('status', 'active')->count();
    }

    public function getPppoeCustomersCountAttribute()
    {
        return $this->customerServices()->where('service_type', 'pppoe')->count();
    }

    public function getHotspotCustomersCountAttribute()
    {
        return $this->customerServices()->where('service_type', 'hotspot')->count();
    }

    public function getVoucherCustomersCountAttribute()
    {
        return $this->customerServices()->where('service_type', 'voucher')->count();
    }

    public function getEstimatedMonthlyRevenueAttribute()
    {
        $activeCount = $this->active_customers_count;
        $price = $this->promo_price ?? $this->base_price ?? 0;
        return $activeCount * $price;
    }
}

