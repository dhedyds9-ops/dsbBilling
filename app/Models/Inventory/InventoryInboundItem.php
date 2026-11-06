<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryInboundItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_inbound_items';

    protected $fillable = [
        'inbound_id',
        'item_id',
        'quantity',
        'unit_cost',
        'subtotal',
        'serials',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'serials' => 'array',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(InventoryInbound::class, 'inbound_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
