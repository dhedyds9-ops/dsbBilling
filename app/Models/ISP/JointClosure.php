<?php

namespace App\Models\ISP;

use App\Livewire\Gis\GisMap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JointClosure extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasBranchScope;

    protected static function booted()
    {
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
        'branch_id',
        'reseller_id',
        'vendor_id',
        'odc_id',
        'olt_id',
        'parent_closure_id',
        'pon_port',
        'code',
        'name',
        'description',
        'type',
        'port_count',
        'address',
        'latitude',
        'longitude',
        'status',
        'photos',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'photos' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForReseller($query, $user = null)
    {
        $user = $user ?? auth()->user();
        if ($user && $user->hasRole('reseller')) {
            $effectiveId = $user->getEffectiveResellerId();
            return $query->where(function ($q) use ($effectiveId) {
                if ($effectiveId) {
                    $q->where('reseller_id', $effectiveId)
                      ->orWhere('created_by', $effectiveId)
                      ->orWhereNull('reseller_id');
                }
            });
        }
        return $query;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function odc()
    {
        return $this->belongsTo(Odc::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function parentClosure()
    {
        return $this->belongsTo(JointClosure::class, 'parent_closure_id');
    }

    public function childClosures()
    {
        return $this->hasMany(JointClosure::class, 'parent_closure_id');
    }

    public function odps()
    {
        return $this->hasMany(Odp::class);
    }

    public function odcs()
    {
        return $this->hasMany(Odc::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function reseller()
    {
        return $this->belongsTo(\App\Models\Reseller::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
