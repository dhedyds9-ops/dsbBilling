<?php

namespace App\Models\ISP;

use App\Livewire\Gis\GisMap;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odp extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->branch_id)) {
                if (!empty($model->odc_id)) {
                    $model->branch_id = Odc::withoutGlobalScopes()->where('id', $model->odc_id)->value('branch_id');
                }
                if (empty($model->branch_id) && !empty($model->olt_id)) {
                    $model->branch_id = Olt::withoutGlobalScopes()->where('id', $model->olt_id)->value('branch_id');
                }
            }
        });

        static::saved(function () {
            GisMap::flushCache();
        });

        static::deleted(function () {
            GisMap::flushCache();
        });

        static::restored(function () {
            GisMap::flushCache();
        });
    }

    protected $fillable = [
        'odc_id',
        'olt_id',
        'pon_port_id',
        'splitter_id',
        'parent_odp_id',
        'code',
        'name',
        'description',
        'address',
        'province',
        'regency',
        'district',
        'village',
        'postal_code',
        'latitude',
        'longitude',
        'split_ratio',
        'port_count',
        'used_port_count',
        'reserved_port_count',
        'status',
        'branch_id',
        'installation_date',
        'last_maintenance_at',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'split_ratio' => 'integer',
        'port_count' => 'integer',
        'used_port_count' => 'integer',
        'reserved_port_count' => 'integer',
        'installation_date' => 'date',
        'last_maintenance_at' => 'datetime',
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
                // 1. ODP berdasarkan Cabang Kantor (branch_id) & kata kunci wilayah
                \App\Services\Auth\ResellerScopeHelper::applyOdpTerritory($q, $ctx, 'odps');

                // 2. ODP yang dibuat oleh reseller ini
                if ($resellerId) {
                    $q->orWhere('odps.created_by', $resellerId);
                }

                // 3. ODP di bawah ODC / OLT wilayah reseller (selama tidak dikunci ke cabang lain)
                $q->orWhere(function ($inherit) use ($ctx, $branchId) {
                    if ($branchId) {
                        $inherit->where(function ($b) use ($branchId) {
                            $b->whereNull('odps.branch_id')->orWhere('odps.branch_id', $branchId);
                        });
                    }
                    $inherit->where(function ($parentQ) use ($ctx) {
                        $parentQ->whereHas('odc', function ($odcQ) use ($ctx) {
                            $odcQ->where(function ($cSub) use ($ctx) {
                                \App\Services\Auth\ResellerScopeHelper::applyOdcTerritory($cSub, $ctx, 'odcs');
                            });
                        })->orWhereHas('olt', function ($oltQ) use ($ctx) {
                            $oltQ->where(function ($lSub) use ($ctx) {
                                \App\Services\Auth\ResellerScopeHelper::applyOltTerritory($lSub, $ctx, 'olts');
                            });
                        });
                    });
                });

                // 4. ODP yang melayani pelanggan / ONU milik reseller ini
                if ($resellerId) {
                    $q->orWhereHas('onus', function ($onuQ) use ($resellerId) {
                        $onuQ->withoutGlobalScopes()->where(function ($uSub) use ($resellerId) {
                            \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($uSub, $resellerId, 'onus');
                        });
                    });
                }
            });
        }
        return $query;
    }

    public function scopeHasGps($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeNear($query, float $lat, float $lng, int $radiusKm = 5)
    {
        $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude))
            * cos(radians(longitude) - radians($lng))
            + sin(radians($lat)) * sin(radians(latitude))))";
        return $query->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", [$radiusKm])
            ->orderBy('distance');
    }

    public function odc()
    {
        return $this->belongsTo(Odc::class);
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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_odp_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_odp_id');
    }

    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function distributionBoxes()
    {
        return $this->hasMany(DistributionBox::class);
    }

    public function customerServices()
    {
        return $this->hasManyThrough(\App\Models\Customer\CustomerService::class, Onu::class);
    }

    public function customers()
    {
        return $this->hasManyThrough(\App\Models\CRM\Customer::class, \App\Models\Customer\CustomerService::class, 'onu_id', 'id', 'id', 'customer_id')
            ->whereHas('onu', fn ($q) => $q->where('odp_id', $this->id));
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function occupancyPercent(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $total = max(1, (int)($this->port_count ?? 16));
                $used = (int)($this->used_port_count ?? 0);
                return round(min(100, ($used / $total) * 100), 1);
            }
        );
    }

    public function availablePortCount(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $total = (int)($this->port_count ?? 16);
                $used = (int)($this->used_port_count ?? 0);
                $reserved = (int)($this->reserved_port_count ?? 0);
                return max(0, $total - $used - $reserved);
            }
        );
    }

    public function gpsAvailable(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->latitude !== null && $this->longitude !== null
        );
    }

    public function getPopAttribute()
    {
        return $this->olt?->pop ?? $this->odc?->pop;
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Master\Branch::class);
    }
}
