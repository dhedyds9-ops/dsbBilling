<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MACAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mac_addresses';

    protected $fillable = [
        'mac_address',
        'asset_id',
        'interface_type',
        'interface_name',
        'is_active',
        'registered_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function getNormalizedMAC(): string
    {
        return strtoupper(str_replace([':', '-', '.'], '', $this->mac_address));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByMAC($query, $mac)
    {
        $normalized = strtoupper(str_replace([':', '-', '.'], '', $mac));
        return $query->where('mac_address', $normalized);
    }
}
