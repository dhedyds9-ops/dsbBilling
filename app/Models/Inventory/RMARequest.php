<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RMARequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rma_requests';

    protected $fillable = [
        'rma_number',
        'asset_id',
        'customer_id',
        'vendor_id',
        'status',
        'requested_at',
        'reason',
        'description',
        'approved_by',
        'approved_at',
        'shipped_by',
        'shipped_at',
        'received_by',
        'received_at',
        'technician_id',
        'diagnosed_at',
        'diagnosis',
        'repair_by',
        'repaired_at',
        'repair_notes',
        'replaced_by',
        'replaced_at',
        'replacement_asset_id',
        'resolved_at',
        'closed_at',
        'returned_at',
        'return_shipped_by',
        'cost',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'diagnosed_at' => 'datetime',
        'repaired_at' => 'datetime',
        'replaced_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'returned_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function replacementAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'replacement_asset_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'requested';
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, ['approved', 'shipped', 'received', 'inspecting', 'repairing', 'replacing']);
    }

    public function isComplete(): bool
    {
        return in_array($this->status, ['resolved', 'closed', 'returned']);
    }
}
