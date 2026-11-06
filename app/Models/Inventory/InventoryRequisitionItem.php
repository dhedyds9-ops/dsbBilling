<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryRequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'item_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_dispatched',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_approved' => 'decimal:2',
        'quantity_dispatched' => 'decimal:2',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(InventoryRequisition::class, 'requisition_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
