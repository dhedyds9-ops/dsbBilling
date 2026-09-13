<?php

namespace App\Models\ISP;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected $table = 'vouchers';

    protected $fillable = [
        'uuid',
        'code',
        'voucher_pool_id',
        'service_profile_id',
        'nas_device_id',
        'reseller_id',   // Reseller yang menerbitkan voucher ini (rename dari owner_id)
        'hotspot_user_id',
        'status',
        'type',
        'bind_on_login',
        'fee_seller',    // Dihitung otomatis dari SettlementCalculator, JANGAN diinput manual
        'login_method',
        'code_combination',
        'activated_at',
        'expires_at',
        'validity_days',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'bind_on_login' => 'boolean',
        'fee_seller' => 'decimal:2',
    ];

    public function voucherPool()
    {
        return $this->belongsTo(VoucherPool::class);
    }

    public function serviceProfile()
    {
        return $this->belongsTo(\App\Models\ISP\ServiceProfile::class);
    }

    public function hotspotUser()
    {
        return $this->belongsTo(HotspotUser::class);
    }

    public function customer()
    {
        // If the voucher is used by a hotspot user, get the customer from there
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class,
            HotspotUser::class,
            'id', // Foreign key on HotspotUser...
            'id', // Foreign key on Customer...
            'hotspot_user_id', // Local key on Voucher...
            'customer_service_id' // Local key on HotspotUser...
        )->with('customerService');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nasDevice()
    {
        return $this->belongsTo(NasDevice::class);
    }

    /**
     * Reseller yang menerbitkan/memiliki voucher ini.
     * Rename dari owner_id untuk kejelasan semantik bisnis.
     * Reseller adalah financial actor (role=reseller).
     */
    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Alias backward-compatible untuk relasi reseller.
     * Digunakan oleh VoucherPrintController, VoucherExport, dan template
     * voucher legacy yang masih memakai nama "owner" (sebelum di-refactor
     * menjadi "reseller" untuk kejelasan semantik).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Alias untuk createdBy (backward-compatible & short-hand di blade).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
