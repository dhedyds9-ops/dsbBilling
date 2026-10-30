<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Onu extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, \App\Traits\HasUserAreaScope, SoftDeletes;

    protected $hidden = ['wifi_password', 'admin_password'];
    protected $fillable = [
        'branch_id',
        'olt_id',
        'pon_port_id',
        'splitter_id',
        'odp_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'mac_address',
        'pon_port',
        'onu_id_on_olt',
        'profile_name',
        'wifi_ssid',
        'wifi_password',
        'admin_password',
        'rx_power_dbm',
        'tx_power_dbm',
        'snr_db',
        'temperature',
        'firmware_version',
        'hardware_version',
        'last_seen_at',
        'provisioned_at',
        'status',
        'provision_status',
        'latitude',
        'longitude',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->branch_id)) {
                if (!empty($model->olt_id)) {
                    $model->branch_id = Olt::withoutGlobalScopes()->where('id', $model->olt_id)->value('branch_id');
                }
                if (empty($model->branch_id) && !empty($model->odp_id)) {
                    $model->branch_id = Odp::withoutGlobalScopes()->where('id', $model->odp_id)->value('branch_id');
                }
            }
            if (empty($model->code)) {
                $prefix = 'ONU-' . strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $model->serial_number ?? Str::random(6)), -6));
                $base = $prefix;
                $counter = 1;
                while (static::withTrashed()->where('code', $prefix)->where('id', '!=', $model->id)->exists()) {
                    $prefix = $base . '-' . $counter++;
                }
                $model->code = $prefix;
            }
            if (!empty($model->serial_number)) {
                $model->serial_number = strtoupper(trim($model->serial_number));
            }
            if (!empty($model->mac_address)) {
                $cleaned = strtoupper(preg_replace('/[^A-F0-9]/i', '', trim($model->mac_address)));
                if (strlen($cleaned) === 12) {
                    $model->mac_address = implode(':', str_split($cleaned, 2));
                }
            }
            if (!empty($model->code)) {
                $model->code = trim($model->code);
            }
            if (!empty($model->name)) {
                $model->name = trim($model->name);
            }
        });

        static::saved(function ($model) {
            Cache::forget('gis_map_data');
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('assets')) {
                    $sn = $model->serial_number ?: ($model->code ?: 'ONU-' . $model->id);
                    $code = $model->code ?: ('ONU-' . substr(preg_replace('/[^A-Za-z0-9]/', '', $sn), -6));
                    $name = $model->name ?: ('ONU Modem ' . ($model->model ?: $sn));
                    $status = ($model->status === 'active' || $model->provision_status === 'success' || !empty($model->provisioned_at)) ? 'in_use' : 'available';

                    \App\Models\Inventory\Asset::updateOrCreate(
                        ['serial_number' => $sn],
                        [
                            'code' => $code,
                            'name' => $name,
                            'type' => 'onu',
                            'status' => $status,
                            'condition' => 'good',
                            'mac_address' => $model->mac_address,
                            'installed_at' => $model->provisioned_at ?: ($model->created_at ?: now()),
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function ($model) {
            Cache::forget('gis_map_data');
        });
    }



    protected $casts = [
        'pon_port' => 'integer',
        'onu_id_on_olt' => 'integer',
        'rx_power_dbm' => 'float',
        'tx_power_dbm' => 'float',
        'snr_db' => 'float',
        'temperature' => 'float',
        'last_seen_at' => 'datetime',
        'provisioned_at' => 'datetime',
        'attributes' => 'array',
    ];



    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForReseller($query, $user = null)
    {
        $user = $user ?? auth()->user();
        if ($user && $user->hasRole('reseller')) {
            $ctx = \App\Services\Auth\ResellerScopeHelper::resolveContext($user);
            $resellerId = $ctx['reseller_id'];
            $branchId = $ctx['branch_id'];

            return $query->where(function ($q) use ($ctx, $resellerId, $branchId) {
                // 1. Kepemilikan langsung (ONU pelanggan reseller ini / didaftarkan oleh reseller ini)
                if ($resellerId) {
                    \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($q, $resellerId, 'onus');
                }

                // 2. ONU di wilayah Cabang / OLT / ODP reseller (kecuali yang sudah terikat ke pelanggan reseller lain)
                if (\App\Services\Auth\ResellerScopeHelper::hasTerritory($ctx)) {
                    $q->orWhere(function ($terrQ) use ($ctx, $resellerId, $branchId) {
                        if ($resellerId) {
                            \App\Services\Auth\ResellerScopeHelper::excludeOtherResellersCustomers($terrQ, $resellerId);
                        }

                        $terrQ->where(function ($matchQ) use ($ctx, $branchId) {
                            if ($branchId) {
                                $matchQ->orWhere('onus.branch_id', $branchId);
                            }

                            $matchQ->orWhere(function ($parentQ) use ($ctx, $branchId) {
                                if ($branchId) {
                                    $parentQ->where(function ($b) use ($branchId) {
                                        $b->whereNull('onus.branch_id')->orWhere('onus.branch_id', $branchId);
                                    });
                                }

                                $parentQ->where(function ($relQ) use ($ctx) {
                                    $relQ->whereHas('olt', function ($oltQ) use ($ctx) {
                                        $oltQ->where(function ($oSub) use ($ctx) {
                                            \App\Services\Auth\ResellerScopeHelper::applyOltTerritory($oSub, $ctx, 'olts');
                                        });
                                    })->orWhereHas('odp', function ($odpQ) use ($ctx) {
                                        $odpQ->where(function ($pSub) use ($ctx) {
                                            \App\Services\Auth\ResellerScopeHelper::applyOdpTerritory($pSub, $ctx, 'odps');
                                        });
                                    });
                                });
                            });
                        });
                    });
                }

                if (!$resellerId && !\App\Services\Auth\ResellerScopeHelper::hasTerritory($ctx)) {
                    $q->whereRaw('1 = 0');
                }
            });
        }
        return $query;
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'active')->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    public function scopeCritical($query)
    {
        $threshold = config('olt-drivers.thresholds.onu_rx_power_critical_low', -28.0);
        return $query->whereNotNull('rx_power_dbm')->where('rx_power_dbm', '<=', $threshold);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function ponPort()
    {
        return $this->belongsTo(PonPort::class);
    }

    public function splitter()
    {
        return $this->belongsTo(Splitter::class);
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function onuPorts()
    {
        return $this->hasMany(OnuPort::class);
    }

    public function powerSupplies()
    {
        return $this->morphMany(PowerSupply::class, 'device');
    }

    public function ups()
    {
        return $this->morphMany(Ups::class, 'device');
    }

    public function signals()
    {
        return $this->hasMany(OnuSignal::class)->latest('measured_at')->limit(50);
    }

    public function latestSignal()
    {
        return $this->hasOne(OnuSignal::class)->latest('measured_at');
    }

    public function acsDevice()
    {
        return $this->hasOne(\App\Models\ACS\ACSDevice::class, 'serial_number', 'serial_number');
    }

    public function customerService()
    {
        return $this->hasOne(\App\Models\Customer\CustomerService::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(
            \App\Models\CRM\Customer::class,
            \App\Models\Customer\CustomerService::class,
            'onu_id',
            'id',
            'id',
            'customer_id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function ipAddress(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                return $this->acsDevice?->ip_address
                    ?? $this->customerService?->pppoeUser?->static_ip
                    ?? $this->customerService?->pppoeUser?->ipAllocation?->ip_address
                    ?? ($this->attributes['ip_address'] ?? null);
            }
        );
    }

    public function isOnline(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) <= 5;
            }
        );
    }

    public function signalQuality(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->rx_power_dbm === null) {
                    return 'unknown';
                }
                $rx = (float)$this->rx_power_dbm;
                return match (true) {
                    $rx >= -20.0 && $rx <= -10.0 => 'excellent',
                    $rx >= -25.0 && $rx <= -8.0  => 'good',
                    $rx >= -28.0                 => 'warning',
                    default                       => 'critical',
                };
            }
        );
    }

    public function genieacsDeviceId(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->serial_number ?: $this->mac_address ?: (string)$this->getKey()
        );
    }

    public function rxPower(): Attribute
    {
        return Attribute::make(
            get: fn (): ?float => $this->rx_power_dbm
        );
    }

    public function txPower(): Attribute
    {
        return Attribute::make(
            get: fn (): ?float => $this->tx_power_dbm
        );
    }

    public function formattedPonPort(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->pon_port === null) return '-';
                if ($this->pon_port >= 1310721 && $this->pon_port <= 1310728) {
                    $port = $this->pon_port - 1310720;
                    return "0/0/{$port}";
                }
                if ($this->pon_port > 1000000) {
                    $slot = ($this->pon_port >> 24) & 0xFF;
                    $port = ($this->pon_port >> 8) & 0xFF;
                    if ($slot === 0 && $port === 0) {
                        return (string)$this->pon_port;
                    }
                    return "0/{$slot}/{$port}";
                }
                return (string)$this->pon_port;
            }
        );
    }

    public function capability()
    {
        return $this->hasOne(OnuCapability::class);
    }

    public function parameterMappings()
    {
        return $this->hasMany(OnuParameterMapping::class);
    }

    public function state()
    {
        return $this->hasOne(OnuState::class);
    }

    public function configurationJobs()
    {
        return $this->hasMany(OnuConfigurationJob::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Master\Branch::class);
    }
}

