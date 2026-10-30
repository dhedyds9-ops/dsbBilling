<?php

namespace App\Models\ACS;

use App\Models\Customer\CustomerService;
use App\Models\Inventory\Asset;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Odp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ACSDevice extends Model
{
    use HasFactory, \App\Traits\HasUserAreaScope, SoftDeletes;

    protected $table = 'acs_devices';

    protected $fillable = [
        'uuid',
        'serial_number',
        'mac_address',
        'oui',
        'manufacturer',
        'vendor_id',
        'model',
        'product_class',
        'hardware_version',
        'software_version',
        'firmware_version',
        'ip_address',
        'connection_request_url',
        'last_inform',
        'last_contact',
        'status',
        'customer_service_id',
        'asset_id',
        'onu_id',
        'olt_id',
        'pop_id',
        'odp_id',
        'latitude',
        'longitude',
        'signal',
        'uptime',
        'cpu',
        'memory',
        'temperature',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'last_inform' => 'datetime',
        'last_contact' => 'datetime',
        'uptime' => 'integer',
        'cpu' => 'float',
        'memory' => 'float',
        'temperature' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeForReseller($query, $user = null)
    {
        $user = $user ?? auth()->user();
        if ($user && $user->hasRole('reseller')) {
            $ctx = \App\Services\Auth\ResellerScopeHelper::resolveContext($user);
            $resellerId = $ctx['reseller_id'];

            return $query->where(function ($q) use ($user, $ctx, $resellerId) {
                // 1. Kepemilikan langsung via CustomerService, ONU pelanggan, atau created_by
                if ($resellerId) {
                    $q->orWhere('acs_devices.created_by', $resellerId)
                      ->orWhereHas('customerService', function ($csQ) use ($resellerId) {
                          $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                              ->where(function ($sub) use ($resellerId) {
                                  $sub->where('customer_services.reseller_id', $resellerId)
                                      ->orWhere('customer_services.created_by', $resellerId)
                                      ->orWhereHas('customer', function ($cq) use ($resellerId) {
                                          $cq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                             ->where(function ($cSub) use ($resellerId) {
                                                 $cSub->where('customers.reseller_id', $resellerId)
                                                      ->orWhere('customers.created_by', $resellerId);
                                             });
                                      });
                              });
                      })
                      ->orWhereHas('onu', function ($onuQ) use ($resellerId) {
                          $onuQ->withoutGlobalScopes(['branch_isolation'])
                               ->where(function ($uSub) use ($resellerId) {
                                   \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($uSub, $resellerId, 'onus');
                               });
                      });
                }

                // 2. Relasi ke ONU / Serial Number / OLT / ODP di wilayah reseller
                //    (mengecualikan device yang sudah terikat ke pelanggan reseller lain)
                $q->orWhere(function ($terrQ) use ($user, $ctx, $resellerId) {
                    if ($resellerId) {
                        \App\Services\Auth\ResellerScopeHelper::excludeOtherResellersCustomers($terrQ, $resellerId);
                    }

                    $terrQ->where(function ($linkQ) use ($user, $ctx) {
                        $linkQ->whereHas('onu', function ($onuQ) use ($user) {
                            $onuQ->forReseller($user);
                        })->orWhereIn(
                            'acs_devices.serial_number',
                            Onu::forReseller($user)
                                ->whereNotNull('onus.serial_number')
                                ->where('onus.serial_number', '!=', '')
                                ->select('onus.serial_number')
                        );

                        if (\App\Services\Auth\ResellerScopeHelper::hasTerritory($ctx)) {
                            $linkQ->orWhereHas('olt', function ($oltQ) use ($ctx) {
                                $oltQ->where(function ($oSub) use ($ctx) {
                                    \App\Services\Auth\ResellerScopeHelper::applyOltTerritory($oSub, $ctx, 'olts');
                                });
                            })->orWhereHas('odp', function ($odpQ) use ($ctx) {
                                $odpQ->where(function ($pSub) use ($ctx) {
                                    \App\Services\Auth\ResellerScopeHelper::applyOdpTerritory($pSub, $ctx, 'odps');
                                });
                            });
                        }
                    });
                });
            });
        }
        return $query;
    }

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class,
            CustomerService::class,
            'id',
            'id',
            'customer_service_id',
            'customer_id'
        );
    }

    public function getResolvedCustomerAttribute()
    {
        $user = auth()->user();
        $isReseller = $user && $user->hasRole('reseller');
        $resellerId = $isReseller ? ($user->getEffectiveResellerId() ?: $user->id) : null;

        $canShowCustomer = function ($customer) use ($isReseller, $resellerId): bool {
            if (!$customer) {
                return false;
            }
            if (!$isReseller) {
                return true;
            }
            return empty($customer->reseller_id)
                || (int) $customer->reseller_id === (int) $resellerId
                || (int) ($customer->created_by ?? 0) === (int) $resellerId;
        };

        if ($this->customer_service_id) {
            $cs = $this->relationLoaded('customerService') && $this->customerService
                ? $this->customerService
                : CustomerService::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->find($this->customer_service_id);
            if ($cs && $cs->customer_id) {
                $cust = $cs->relationLoaded('customer') && $cs->customer
                    ? $cs->customer
                    : \App\Models\CRM\Customer::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->find($cs->customer_id);
                if ($canShowCustomer($cust)) {
                    return $cust;
                }
            }
        }

        $onu = null;
        if ($this->onu_id) {
            $onu = $this->relationLoaded('onu') && $this->onu
                ? $this->onu
                : Onu::withoutGlobalScopes(['branch_isolation'])->find($this->onu_id);
        } elseif (!empty($this->serial_number)) {
            $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $this->serial_number)->first();
        }

        if ($onu) {
            $cs = $onu->relationLoaded('customerService') && $onu->customerService
                ? $onu->customerService
                : CustomerService::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->where('onu_id', $onu->id)->first();
            if ($cs && $cs->customer_id) {
                $cust = $cs->relationLoaded('customer') && $cs->customer
                    ? $cs->customer
                    : \App\Models\CRM\Customer::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->find($cs->customer_id);
                if ($canShowCustomer($cust)) {
                    return $cust;
                }
            }
        }

        return null;
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\ISP\Vendor::class);
    }

    public function tasks()
    {
        return $this->hasMany(DeviceTask::class, 'acs_device_id');
    }

    public function alarms()
    {
        return $this->hasMany(ACSAlarm::class, 'acs_device_id');
    }

    public function logs()
    {
        return $this->hasMany(ACSLog::class, 'acs_device_id');
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
