<?php

namespace App\Models\Inventory;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryDispatch extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'uuid',
        'dispatch_number',
        'requisition_id',
        'warehouse_id',
        'branch_id',
        'distribution_method',
        'recipient_id',
        'recipient_type',
        'customer_id',
        'installation_id',
        'ticket_id',
        'status',
        'dispatched_by',
        'dispatched_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->dispatch_number)) {
                $prefix = 'DSP-' . date('Ym') . '-';
                $latest = static::withoutGlobalScopes()
                    ->where('dispatch_number', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->dispatch_number, $matches)) {
                    $nextNum = (int) $matches[1] + 1;
                }

                $model->dispatch_number = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }

            if (empty($model->dispatched_at)) {
                $model->dispatched_at = now();
            }
        });
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(InventoryRequisition::class, 'requisition_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
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

    public function items(): HasMany
    {
        return $this->hasMany(InventoryDispatchItem::class, 'dispatch_id');
    }

    public function custodies(): HasMany
    {
        return $this->hasMany(InventoryCustody::class, 'dispatch_id');
    }
}
