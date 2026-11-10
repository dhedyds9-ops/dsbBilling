<?php

namespace App\Models\Support;

use App\Models\CRM\Customer;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportMaintenance extends Model
{
    use HasFactory, HasBranchScope;

    protected $table = 'support_maintenances';

    protected $fillable = [
        'branch_id',
        'ticket_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'title',
        'type',
        'source',
        'status',
        'priority',
        'device_type',
        'device_name',
        'location_name',
        'scheduled_date',
        'scheduled_time',
        'completed_at',
        'assigned_to',
        'notes',
        'resolution_notes',
        'qc_verified_by',
        'qc_verified_at',
        'qc_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'qc_verified_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function qcVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_verified_by');
    }
}
