<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryReturn extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'uuid',
        'return_number',
        'returner_id',
        'returner_type',
        'warehouse_id',
        'status',
        'inspected_by',
        'inspected_at',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->return_number)) {
                $prefix = 'RET-' . date('Ym') . '-';
                $latest = static::withoutGlobalScopes()
                    ->where('return_number', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->return_number, $matches)) {
                    $nextNum = (int) $matches[1] + 1;
                }

                $model->return_number = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returner_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReturnItem::class, 'return_id');
    }
}
