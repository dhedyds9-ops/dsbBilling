<?php

namespace App\Models\AAA;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vouchers';

    protected $fillable = [
        'uuid',
        'code',
        'voucher_pool_id',
        'service_profile_id',
        'hotspot_user_id',
        'status',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
