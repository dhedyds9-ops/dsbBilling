<?php

namespace App\Models\Customer;

use App\Traits\HasResellerScope;

use App\Models\CRM\Customer;
use App\Models\CRM\Contract;
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
    use HasResellerScope;

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
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }

            if (empty($model->reseller_id) && !empty($model->customer_id)) {
                $model->reseller_id = \App\Models\CRM\Customer::withoutGlobalScopes()->where('id', $model->customer_id)->value('reseller_id');
            }

            if (empty($model->contract_id) && !empty($model->customer_id)) {
                $contract = \App\Models\CRM\Contract::firstOrCreate(
                    ['customer_id' => $model->customer_id],
                    [
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'contract_number' => 'CTR-' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'start_date' => now(),
                        'status' => 'active',
                        'created_by' => $model->created_by,
                        'updated_by' => $model->updated_by,
                    ]
                );
                $model->contract_id = $contract->id;
            }

            if (empty($model->service_id)) {
                $service = \App\Models\ServiceCatalog\Service::first();
                if (!$service) {
                    try {
                        $catalog = \App\Models\ServiceCatalog\ServiceCatalog::withoutGlobalScopes()->firstOrCreate(
                            ['name' => 'Layanan Internet'],
                            [
                                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                                'description' => 'Katalog Layanan Default',
                                'is_active' => true,
                                'created_by' => $model->created_by,
                                'updated_by' => $model->updated_by,
                            ]
                        );
                        $service = \App\Models\ServiceCatalog\Service::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'service_catalog_id' => $catalog->id,
                            'name' => 'Default Internet Service',
                            'description' => 'Auto-generated service',
                            'type' => 'hotspot',
                            'price' => 0,
                            'is_active' => true,
                            'created_by' => $model->created_by,
                            'updated_by' => $model->updated_by,
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('CustomerService auto service fallback: ' . $e->getMessage());
                    }
                }
                if ($service) {
                    $model->service_id = $service->id;
                }
            }

            if (empty($model->username)) {
                $model->username = 'cs_' . ($model->customer_id ?? time()) . '_' . \Illuminate\Support\Str::random(4);
            }

            if (empty($model->password)) {
                $model->password = \Illuminate\Support\Str::random(8);
            }
        });

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

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
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

    public function getServiceTypeAttribute(): ?string
    {
        return $this->serviceProfile->service_type ?? ($this->attributes['service_type'] ?? null);
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


