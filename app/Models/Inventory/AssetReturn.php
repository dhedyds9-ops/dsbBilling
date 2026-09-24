<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_returns';

    protected $fillable = [
        'asset_id',
        'returned_by',
        'received_by',
        'returned_at',
        'condition',
        'notes',
        'assignment_id',
        'reason',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function requiresInspection(): bool
    {
        return in_array($this->condition, ['damaged', 'poor']);
    }

    public function requiresRepair(): bool
    {
        return $this->condition === 'damaged';
    }
}
