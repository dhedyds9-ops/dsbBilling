<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssetAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_id',
        'assigned_to_id',
        'assigned_to_type',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'return_accepted_by',
        'notes',
        'condition_upon_return',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assignedTo(): MorphTo
    {
        return $this->morphTo('assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return $this->returned_at === null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }
}
