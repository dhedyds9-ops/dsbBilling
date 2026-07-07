<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MaterialConsumption extends Model {
    use HasUuids;

    protected $fillable = [
        'task_id',
        'inventory_item_id',
        'quantity',
        'notes',
    ];
}
