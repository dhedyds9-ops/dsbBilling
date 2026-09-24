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
        'material_id',
        'material_name',
        'quantity',
        'serial_number',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
