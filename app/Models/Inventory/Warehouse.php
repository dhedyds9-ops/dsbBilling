<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $table = 'warehouses';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'type', // main, branch, transit
        'branch_id',
        'manager_id',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_active',
        'allows_returns',
        'capacity',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allows_returns' => 'boolean',
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function booted()
    {
        static::creating(function ($wh) {
            if (empty($wh->uuid)) {
                $wh->uuid = (string) Str::uuid();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'warehouse_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ItemSerial::class, 'warehouse_id');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(InventoryStockMutation::class, 'warehouse_id');
    }

    public function inbounds(): HasMany
    {
        return $this->hasMany(InventoryInbound::class, 'warehouse_id');
    }

    public function isMain(): bool
    {
        return $this->type === 'main';
    }

    public function isBranch(): bool
    {
        return $this->type === 'branch';
    }

    public function isTransit(): bool
    {
        return $this->type === 'transit';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
