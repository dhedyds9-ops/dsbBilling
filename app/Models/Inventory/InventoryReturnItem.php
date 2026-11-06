<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'custody_id',
        'item_id',
        'item_serial_id',
        'quantity',
        'condition',
        'action_taken',
        'qc_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(InventoryReturn::class, 'return_id');
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
