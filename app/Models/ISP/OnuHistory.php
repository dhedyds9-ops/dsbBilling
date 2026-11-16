<?php

namespace App\Models\ISP;

use App\Models\CRM\Customer;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnuHistory extends Model
{
    use HasFactory, HasBranchScope;

    protected $table = 'onu_histories';

    protected $fillable = [
        'transaction_id',
        'onu_id',
        'serial_number',
        'customer_id',
        'old_status',
        'new_status',
        'condition',
        'old_olt_id',
        'new_olt_id',
        'old_odp_id',
        'new_odp_id',
        'activity',
        'technician_id',
        'ticket_id',
        'work_order_id',
        'branch_id',
        'payload',
        'description',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function onu(): BelongsTo
    {
        return $this->belongsTo(Onu::class, 'onu_id')->withoutGlobalScopes();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withoutGlobalScopes();
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id')->withoutGlobalScopes();
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function oldOlt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'old_olt_id')->withoutGlobalScopes();
    }

    public function newOlt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'new_olt_id')->withoutGlobalScopes();
    }

    public function oldOdp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'old_odp_id')->withoutGlobalScopes();
    }

    public function newOdp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'new_odp_id')->withoutGlobalScopes();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
