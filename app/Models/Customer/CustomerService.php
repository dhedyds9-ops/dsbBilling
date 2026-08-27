<?php

namespace App\Models\Customer;

use App\Models\CRM\Customer;
use App\Models\ISP\Onu;
use App\Models\ISP\ServiceProfile;
use App\Models\ServiceCatalog\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'contract_id',
        'service_id',
        'service_profile_id',
        'onu_id',
        'username',
        'password',
        'status',
        'activated_at',
        'suspended_at',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'attributes' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pppoeUser()
    {
        return $this->hasOne(\App\Models\ISP\PPPoEUser::class);
    }

    public function hotspotUser()
    {
        return $this->hasOne(\App\Models\ISP\HotspotUser::class);
    }
}
