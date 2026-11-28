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
        'sla_due_at',
        'sla_started_at',
        'sla_target_minutes',
        'sla_status',
        'estimated_hours',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'qc_verified_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'sla_started_at' => 'datetime',
        'sla_target_minutes' => 'integer',
        'estimated_hours' => 'float',
    ];

    public function formatEstimatedDuration(): string
    {
        if ($this->estimated_hours) {
            $h = (float) $this->estimated_hours;
            return $h >= 24 ? round($h / 24, 1) . ' Hari' : ($h == (int)$h ? (int)$h : $h) . ' Jam';
        }
        if ($this->sla_target_minutes) {
            $m = (int) $this->sla_target_minutes;
            if ($m >= 1440) return round($m / 1440, 1) . ' Hari';
            if ($m >= 60) return round($m / 60, 1) . ' Jam';
            return $m . ' Menit';
        }
        return '24 Jam';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Branch::class, 'branch_id');
    }

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
