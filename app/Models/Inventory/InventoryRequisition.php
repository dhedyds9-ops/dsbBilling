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

class InventoryRequisition extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'uuid',
        'requisition_number',
        'requester_id',
        'requester_type',
        'warehouse_id',
        'branch_id',
        'distribution_method',
        'customer_id',
        'work_order_type',
        'installation_id',
        'ticket_id',
        'designated_technician_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->requisition_number)) {
                $prefix = 'REQ-' . date('Ym') . '-';
                $latest = static::withoutGlobalScopes()
                    ->where('requisition_number', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->requisition_number, $matches)) {
                    $nextNum = (int) $matches[1] + 1;
                }

                $model->requisition_number = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
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

    public function designatedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designated_technician_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryRequisitionItem::class, 'requisition_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(InventoryDispatch::class, 'requisition_id');
    }

    public function custodies(): HasMany
    {
        return $this->hasMany(InventoryCustody::class, 'requisition_id');
    }
}
