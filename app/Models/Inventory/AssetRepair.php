<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRepair extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_repairs';

    protected $fillable = [
        'asset_id',
        'issue_description',
        'technician_id',
        'reported_at',
        'diagnosed_at',
        'repair_started_at',
        'completed_at',
        'returned_at',
        'status',
        'diagnosis',
        'repair_notes',
        'cost',
        'vendor_id',
        'vendor_invoice_number',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'diagnosed_at' => 'datetime',
        'repair_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'returned_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }
}
