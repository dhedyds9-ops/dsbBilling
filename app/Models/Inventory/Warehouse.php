<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'manager_id',
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

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'warehouse_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'warehouse_id');
    }

    public function isMain(): bool
    {
        return $this->type === 'main';
    }

    public function isBranch(): bool
    {
        return $this->type === 'branch';
    }

    public function isTechnicianStock(): bool
    {
        return $this->type === 'technician';
    }

    public function canReceiveStock(): bool
    {
        return $this->is_active && in_array($this->type, ['main', 'branch', 'staging']);
    }

    public function canIssueStock(): bool
    {
        return $this->is_active && in_array($this->type, ['main', 'branch', 'staging', 'technician']);
    }
}


