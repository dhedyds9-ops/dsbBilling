<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rack_units';

    protected $fillable = [
        'rack_id',
        'position',
        'size',
        'is_occupied',
        'asset_id',
        'description',
    ];

    protected $casts = [
        'position' => 'integer',
        'size' => 'integer',
        'is_occupied' => 'boolean',
    ];

    public function isAvailable(): bool
    {
        return !$this->is_occupied;
    }

    public function canAccommodate(int $requiredSize): bool
    {
        return !$this->is_occupied && $this->size >= $requiredSize;
    }
}
