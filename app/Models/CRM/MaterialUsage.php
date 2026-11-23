<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'installation_id',
        'work_order_id',
        'item_id',
        'material_id',
        'material_name',
        'quantity',
        'serial_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(\App\Models\Workforce\WorkOrder::class, 'work_order_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Inventory\Item::class, 'item_id');
    }
}
