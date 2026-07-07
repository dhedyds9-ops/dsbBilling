<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetWarranty extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_warranties';

    protected $fillable = [
        'asset_id',
        'start_date',
        'end_date',
        'status',
        'vendor_id',
        'warranty_type',
        'warranty_number',
        'coverage_details',
        'registered_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registered_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function getRemainingDays(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return $this->end_date->diffInDays(now());
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return !$this->isExpired() && $this->getRemainingDays() <= $days;
    }
}
