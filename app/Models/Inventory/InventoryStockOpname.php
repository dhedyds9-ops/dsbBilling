<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryStockOpname extends Model
{
    use HasFactory, HasBranchScope;

    protected $table = 'inventory_stock_opnames';

    protected $fillable = [
        'uuid',
        'opname_number',
        'warehouse_id',
        'branch_id',
        'status',
        'opname_date',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->opname_number)) {
                $date = now()->format('ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $model->opname_number = sprintf('OPN-%s-%04d', $date, $count);
            }
            if (empty($model->opname_date)) {
                $model->opname_date = today();
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryStockOpnameItem::class, 'opname_id');
    }
}
