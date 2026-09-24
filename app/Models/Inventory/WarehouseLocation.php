<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'warehouse_locations';

    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
        'description',
        'parent_location_id',
        'max_capacity',
        'current_utilization',
        'is_active',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'current_utilization' => 'integer',
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'parent_location_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'parent_location_id');
    }

    public function isAtCapacity(): bool
    {
        if ($this->max_capacity === 0) {
            return false;
        }
        return $this->current_utilization >= $this->max_capacity;
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->max_capacity === 0) {
            return 0;
        }
        return ($this->current_utilization / $this->max_capacity) * 100;
    }
}
