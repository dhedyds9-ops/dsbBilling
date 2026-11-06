<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryCustodyTransfer extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'uuid',
        'transfer_number',
        'custody_id',
        'from_user_id',
        'from_user_type',
        'to_user_id',
        'to_user_type',
        'item_id',
        'item_serial_id',
        'quantity',
        'status',
        'notes',
        'accepted_at',
        'branch_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'accepted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->transfer_number)) {
                $prefix = 'TRF-' . date('Ym') . '-';
                $latest = static::withoutGlobalScopes()
                    ->where('transfer_number', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->transfer_number, $matches)) {
                    $nextNum = (int) $matches[1] + 1;
                }

                $model->transfer_number = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function custody(): BelongsTo
    {
        return $this->belongsTo(InventoryCustody::class, 'custody_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function itemSerial(): BelongsTo
    {
        return $this->belongsTo(ItemSerial::class, 'item_serial_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
