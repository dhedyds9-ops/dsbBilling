<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_transfers';

    protected $fillable = [
        'asset_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'initiated_by',
        'initiated_at',
        'status',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'notes',
        'reason',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInTransit(): bool
    {
        return $this->status === 'shipped';
    }

    public function isComplete(): bool
    {
        return $this->status === 'received';
    }
}
