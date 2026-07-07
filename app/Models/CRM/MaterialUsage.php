<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installation_id',
        'item_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Inventory\Item::class);
    }
}
