<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDispatchItem extends Model
{
    protected $fillable = [
        'dispatch_id',
        'item_id',
        'quantity',
        'serials',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'serials' => 'array',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(InventoryDispatch::class, 'dispatch_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
