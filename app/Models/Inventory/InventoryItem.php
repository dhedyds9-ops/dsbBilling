<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'sku',
        'name',
        'quantity',
        'reserved_quantity',
        'min_stock_level',
        'max_stock_level',
        'status',
        'location_id',
        'rack_id',
        'shelf_number',
        'expiry_date',
        'unit_cost',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isBelowMinStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    public function needsReorder(): bool
    {
        return $this->isBelowMinStock();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
