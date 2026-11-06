<?php

namespace App\Models\Inventory;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryCustody extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'uuid',
        'dispatch_id',
        'requisition_id',
        'item_id',
        'item_serial_id',
        'holder_id',
        'holder_type',
        'customer_id',
        'installation_id',
        'ticket_id',
        'quantity',
        'used_quantity',
        'returned_quantity',
        'status',
        'branch_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'used_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getAvailableQuantityAttribute(): float
    {
        $rem = (float) $this->quantity - (float) $this->used_quantity - (float) $this->returned_quantity;
        return max(0, $rem);
    }

    public function scopeActiveCustody(Builder $query): Builder
    {
        return $query->where('status', 'in_custody')
            ->whereRaw('(quantity - used_quantity - returned_quantity) > 0');
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(InventoryDispatch::class, 'dispatch_id');
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(InventoryRequisition::class, 'requisition_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function itemSerial(): BelongsTo
    {
        return $this->belongsTo(ItemSerial::class, 'item_serial_id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'holder_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(Installation::class, 'installation_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(InventoryCustodyTransfer::class, 'custody_id');
    }
}
