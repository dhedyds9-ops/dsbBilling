<?php

namespace App\Models\ISP;

use App\Livewire\Gis\GisMap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odc extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->parent_odc_id)) {
                $parent = Odc::withoutGlobalScopes()->find($model->parent_odc_id);
                if ($parent) {
                    if (empty($model->olt_id)) {
                        $model->olt_id = $parent->olt_id;
                    }
                    if (empty($model->branch_id)) {
                        $model->branch_id = $parent->branch_id;
                    }
                }
            }
            if (empty($model->branch_id) && !empty($model->olt_id)) {
                $model->branch_id = Olt::withoutGlobalScopes()->where('id', $model->olt_id)->value('branch_id');
            }
        });

        static::saved(function ($model) {
            GisMap::flushCache();
            if ($model->wasChanged('branch_id') && !empty($model->branch_id)) {
                // Otomatis sinkronkan seluruh ODP di bawah ODC ini ke cabang baru
                Odp::withoutGlobalScopes()
                    ->where('odc_id', $model->id)
                    ->update(['branch_id' => $model->branch_id]);
            }
        });

        static::deleted(function () {
            GisMap::flushCache();
        });

        static::restored(function () {
            GisMap::flushCache();
        });
    }

    protected $fillable = [
        'olt_id',
        'parent_odc_id',
        'pop_id',
        'code',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'port_count',
        'active_port_count',
        'status',
        'branch_id',
        'reseller_id',
        'photos',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'active_port_count' => 'integer',
        'photos' => 'array',
    ];

    public function reseller()
    {
        return $this->belongsTo(\App\Models\User::class, 'reseller_id');
    }

    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }

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
                // 1. ODC berdasarkan Cabang Kantor (branch_id) & kata kunci wilayah
                \App\Services\Auth\ResellerScopeHelper::applyOdcTerritory($q, $ctx, 'odcs');

                // 2. ODC yang dibuat atau dimiliki oleh reseller ini
                if ($resellerId) {
                    $q->orWhere('odcs.created_by', $resellerId)
                      ->orWhere('odcs.reseller_id', $resellerId);
                }

                // 3. ODC di bawah OLT wilayah reseller (selama tidak dikunci ke cabang lain)
                $q->orWhere(function ($inherit) use ($ctx, $branchId) {
                    if ($branchId) {
                        $inherit->where(function ($b) use ($branchId) {
                            $b->whereNull('odcs.branch_id')->orWhere('odcs.branch_id', $branchId);
                        });
                    }
                    $inherit->whereHas('olt', function ($oltQ) use ($ctx) {
                        $oltQ->where(function ($oSub) use ($ctx) {
                            \App\Services\Auth\ResellerScopeHelper::applyOltTerritory($oSub, $ctx, 'olts');
                        });
                    });
                });

                // 4. ODC yang membawahi ODP milik wilayah / pelanggan reseller ini
                $q->orWhereHas('odps', function ($odpQ) use ($ctx, $resellerId) {
                    $odpQ->where(function ($oSub) use ($ctx, $resellerId) {
                        \App\Services\Auth\ResellerScopeHelper::applyOdpTerritory($oSub, $ctx, 'odps');
                        if ($resellerId) {
                            $oSub->orWhere('odps.created_by', $resellerId)
                                 ->orWhereHas('onus', function ($onuQ) use ($resellerId) {
                                     $onuQ->withoutGlobalScopes()->where(function ($uSub) use ($resellerId) {
                                         \App\Services\Auth\ResellerScopeHelper::applyOnuOwnership($uSub, $resellerId, 'onus');
                                     });
                                 });
                        }
                    });
                });
            });
        }
        return $query;
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function parentOdc()
    {
        return $this->belongsTo(Odc::class, 'parent_odc_id');
    }

    public function childOdcs()
    {
        return $this->hasMany(Odc::class, 'parent_odc_id');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function odps()
    {
        return $this->hasMany(Odp::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function fiberCablesAsStart()
    {
        return $this->hasMany(FiberCable::class, 'start_odc_id');
    }

    public function fiberCablesAsEnd()
    {
        return $this->hasMany(FiberCable::class, 'end_odc_id');
    }

    public function distributionLinks()
    {
        return $this->hasMany(DistributionLink::class);
    }

    public function powerSupplies()
    {
        return $this->morphMany(PowerSupply::class, 'device');
    }

    public function ups()
    {
        return $this->morphMany(Ups::class, 'device');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Master\Branch::class);
    }
}
