<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'serial_numbers';

    protected $fillable = [
        'serial_number',
        'asset_id',
        'product_id',
        'vendor_id',
        'manufactured_date',
        'received_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'manufactured_date' => 'date',
        'received_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySerial($query, $serial)
    {
        return $query->where('serial_number', $serial);
    }
}
