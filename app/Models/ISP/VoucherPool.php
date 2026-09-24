<?php

namespace App\Models\ISP;

use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voucher_pools';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'service_profile_id',
        'prefix',
        'length',
        'quota',
        'validity_days',
        'total_vouchers',
        'used_vouchers',
        'active_vouchers',
        'status',
        'created_by',
        'updated_by',
    ];

    public function serviceProfile()
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function hotspotUsers()
    {
        return $this->hasMany(HotspotUser::class);
    }
}
