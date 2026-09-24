<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_maintenances';

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'scheduled_date',
        'performed_date',
        'technician_id',
        'status',
        'description',
        'notes',
        'cost',
        'vendor_id',
        'is_recurring',
        'recurrence_interval_days',
        'parent_maintenance_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'performed_date' => 'date',
        'cost' => 'decimal:2',
        'is_recurring' => 'boolean',
        'recurrence_interval_days' => 'integer',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetMaintenance::class, 'parent_maintenance_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_date->isPast();
    }

    public function isDueSoon(int $days = 7): bool
    {
        return $this->status === 'scheduled' 
            && !$this->scheduled_date->isPast()
            && $this->scheduled_date->diffInDays(now()) <= $days;
    }
}
