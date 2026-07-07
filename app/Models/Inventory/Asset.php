<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'assets';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'category_id',
        'warehouse_id',
        'location_id',
        'rack_id',
        'rack_position',
        'status',
        'condition',
        'serial_number',
        'mac_address',
        'purchase_price',
        'purchase_date',
        'warranty_months',
        'warranty_start_date',
        'warranty_end_date',
        'vendor_id',
        'parent_asset_id',
        'assigned_to_id',
        'assigned_to_type',
        'assigned_at',
        'installation_id',
        'installed_at',
        'barcode_path',
        'qrcode_path',
        'custom_fields',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'assigned_at' => 'datetime',
        'installed_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Asset::class, 'parent_asset_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AssetReturn::class, 'asset_id');
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(AssetRepair::class, 'asset_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(AssetWarranty::class, 'asset_id');
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class, 'asset_id');
    }

    public function macAddresses(): HasMany
    {
        return $this->hasMany(MACAddress::class, 'asset_id');
    }

    public function isAvailable(): bool
    {
        return in_array($this->status, ['in_stock', 'reserved']);
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isInstalled(): bool
    {
        return $this->status === 'installed';
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
