<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'stock_movements';

    protected $fillable = [
        'inventory_item_id',
        'movement_type',
        'quantity',
        'from_warehouse_id',
        'to_warehouse_id',
        'from_location_id',
        'to_location_id',
        'reference_id',
        'reference_type',
        'notes',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'performed_at' => 'datetime',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function isIncoming(): bool
    {
        return in_array($this->movement_type, [
            'receive',
            'transfer_in',
            'adjustment_in',
            'return_in',
            'repair_in',
        ]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->movement_type, [
            'issue',
            'transfer_out',
            'adjustment_out',
            'return_out',
            'repair_out',
            'scrap',
            'sold',
        ]);
    }
}
