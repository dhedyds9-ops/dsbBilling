<?php

namespace App\Models\Support;

use App\Traits\HasResellerScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasResellerScope;
    use HasFactory, \App\Traits\HasUserAreaScope, \App\Traits\HasBranchScope;

    protected $fillable = [
        'uuid',
        'customer_id',
        'title',
        'description',
        'resolution_notes',
        'category',
        'priority',
        'status',
        'assigned_to',
        'reseller_id',
        'resolved_at',
        'due_date',
        'photos',
        'optical_power',
        'digital_signature',
        'checklists',
        'requires_field_visit',
        'maintenance_wo_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'date',
        'photos' => 'array',
        'checklists' => 'array',
        'requires_field_visit' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\Customer::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function maintenances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportMaintenance::class, 'ticket_id');
    }

    public function maintenanceWorkOrder(): BelongsTo
    {
        return $this->belongsTo(SupportMaintenance::class, 'maintenance_wo_id');
    }
}
