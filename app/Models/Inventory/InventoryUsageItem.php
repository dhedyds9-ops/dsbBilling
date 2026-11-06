<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryUsageItem extends Model
{
    protected $fillable = [
        'usage_id',
        'custody_id',
        'item_id',
        'item_serial_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function usage(): BelongsTo
    {
        return $this->belongsTo(InventoryUsage::class, 'usage_id');
    }

    public function custody(): BelongsTo
    {
        return $this->belongsTo(InventoryCustody::class, 'custody_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function itemSerial(): BelongsTo
    {
        return $this->belongsTo(ItemSerial::class, 'item_serial_id');
    }
}
