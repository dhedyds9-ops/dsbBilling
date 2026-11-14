<?php

namespace App\Models\Workforce;

use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'type',
        'ticket_id',
        'installation_id',
        'customer_id',
        'assigned_to',
        'status',
        'title',
        'description',
        'notes',
        'scheduled_at',
        'started_at',
        'completed_at',
        'approved_by',
        'approved_at',
        'priority',
        'optical_rx_power',
        'optical_tx_power',
        'customer_signature',
        'evidence_photos',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'optical_rx_power' => 'decimal:2',
        'optical_tx_power' => 'decimal:2',
        'evidence_photos' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\Installation::class, 'installation_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(WorkOrderChecklist::class);
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class);
    }
}
