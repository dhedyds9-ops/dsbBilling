<?php

namespace App\Models\Inventory;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ItemSerial extends Model
{
    use HasFactory, HasBranchScope;

    protected $table = 'item_serials';

    protected $fillable = [
        'uuid',
        'item_id',
        'serial_number',
        'mac_address',
        'warehouse_id',
        'custodian_id',
        'custodian_type', // reseller, technician
        'customer_id',
        'installation_id',
        'ticket_id',
        'status', // in_warehouse, in_custody_reseller, in_custody_technician, installed, returned_check, damaged, scrapped
        'condition', // good, needs_check, damaged
        'branch_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($serial) {
            if (empty($serial->uuid)) {
                $serial->uuid = (string) Str::uuid();
            }
            if (empty($serial->created_by) && auth()->check()) {
                $serial->created_by = auth()->id();
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeInWarehouse($query)
    {
        return $query->where('status', 'in_warehouse');
    }

    public function scopeInCustody($query)
    {
        return $query->whereIn('status', ['in_custody_reseller', 'in_custody_technician']);
    }

    public function scopeInstalled($query)
    {
        return $query->where('status', 'installed');
    }
}
