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
use Illuminate\Support\Facades\Cache;

class CustomerService extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'contract_id',
        'service_id',
        'service_profile_id',
        'network_profile_id',
        'onu_id',
        'username',
        'password',
        'status',
        'reactivation_status',
        'optical_status',
        'tr069_status',
        'service_status',
        'diagnostic_status',
        'last_seen_at',
        'last_state_change_at',
        'offline_reason',
        'activated_at',
        'suspended_at',
        'attributes',
        'reseller_id',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget('gis_map_data');
        });

        static::deleted(function ($model) {
            Cache::forget('gis_map_data');
            
            if (!$model->isForceDeleting()) {
                if ($model->pppoeUser) {
                    $model->pppoeUser->delete();
                }
                if ($model->hotspotUser) {
                    $model->hotspotUser->delete();
                }
            }
        });
        
        static::restoring(function ($model) {
            // Restore related ISP users if they were trashed
            if ($model->pppoeUser()->onlyTrashed()->exists()) {
                $model->pppoeUser()->onlyTrashed()->first()->restore();
            }
            if ($model->hotspotUser()->onlyTrashed()->exists()) {
                $model->hotspotUser()->onlyTrashed()->first()->restore();
            }
        });
    }

    protected $casts = [
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_state_change_at' => 'datetime',
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

    public function networkProfile(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Provisioning\NetworkProfile::class);
    }

    public function acsDevice()
    {
        return $this->hasOne(\App\Models\ACS\ACSDevice::class, 'customer_service_id');
    }
}


