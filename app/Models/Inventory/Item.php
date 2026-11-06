<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $table = 'items';

    protected $fillable = [
        'uuid',
        'sku',
        'name',
        'category',
        'unit',
        'brand',
        'model',
        'description',
        'min_stock',
        'is_serialized',
        'is_reusable',
        'cost_price',
        'selling_price',
        'is_active',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_stock' => 'integer',
        'is_serialized' => 'boolean',
        'is_reusable' => 'boolean',
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
            if (empty($item->created_by) && auth()->check()) {
                $item->created_by = auth()->id();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'item_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ItemSerial::class, 'item_id');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(InventoryStockMutation::class, 'item_id');
    }

    public function getTotalStockAttribute(): float
    {
        return (float) $this->stocks()->sum('quantity');
    }

    public function getAvailableStockAttribute(): float
    {
        return (float) $this->stocks()->sum(\Illuminate\Support\Facades\DB::raw('quantity - reserved_quantity'));
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSerialized($query)
    {
        return $query->where('is_serialized', true);
    }
}
