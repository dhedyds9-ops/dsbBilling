<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockOpnameItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_opname_items';

    protected $fillable = [
        'opname_id',
        'item_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'adjustment_reason',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'difference_quantity' => 'decimal:2',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(InventoryStockOpname::class, 'opname_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
