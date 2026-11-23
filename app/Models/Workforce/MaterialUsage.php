<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'work_order_id',
        'installation_id',
        'item_id',
        'material_id',
        'material_name',
        'quantity',
        'serial_number',
        'notes',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\Installation::class, 'installation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\Item::class, 'item_id');
    }
}
