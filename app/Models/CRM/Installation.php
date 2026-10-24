<?php

namespace App\Models\CRM;

use App\Models\CRM\Contract;
use App\Models\CRM\Customer;
use App\Models\ISP\Onu;
use App\Models\ISP\Pop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Installation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'survey_id',
        'customer_id',
        'customer_name',
        'contract_id',
        'onu_id',
        'assigned_to',
        'technician_id',
        'scheduled_at',
        'scheduled_date',
        'scheduled_time',
        'completed_at',
        'status',
        'priority',
        'pop_id',
        'onu_serial_number',
        'onu_mac_address',
        'router_model',
        'router_serial_number',
        'digital_signature',
        'photos',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'photos' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::saving(function ($model) {
            // Keep assigned_to and technician_id in sync
            if (!empty($model->assigned_to) && empty($model->technician_id)) {
                $model->technician_id = $model->assigned_to;
            } elseif (!empty($model->technician_id) && empty($model->assigned_to)) {
                $model->assigned_to = $model->technician_id;
            }

            // Keep scheduled_at and scheduled_date/time in sync
            if (!empty($model->scheduled_date)) {
                $time = $model->scheduled_time ?: '09:00:00';
                $dateStr = is_string($model->scheduled_date) ? $model->scheduled_date : $model->scheduled_date->format('Y-m-d');
                $model->scheduled_at = Carbon::parse($dateStr . ' ' . $time);
            } elseif (!empty($model->scheduled_at)) {
                $model->scheduled_date = $model->scheduled_at->format('Y-m-d');
                $model->scheduled_time = $model->scheduled_at->format('H:i');
            }
        });
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class, 'pop_id');
    }

    public function checklists()
    {
        return $this->hasMany(InstallationChecklist::class);
    }

    public function materials()
    {
        return $this->hasMany(MaterialUsage::class);
    }

    public function qualityControl()
    {
        return $this->hasOne(QualityControl::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if (!empty($this->customer_name)) {
            return $this->customer_name;
        }
        if ($this->customer?->name) {
            return $this->customer->name;
        }
        if ($this->survey?->prospect?->name) {
            return $this->survey->prospect->name;
        }
        if ($this->contract?->customer?->name) {
            return $this->contract->customer->name;
        }
        return 'Pelanggan #' . $this->id;
    }

    public function getTechnicianDisplayNameAttribute(): string
    {
        if ($this->assignedTo?->name) {
            return $this->assignedTo->name;
        }
        if ($this->technician?->name) {
            return $this->technician->name;
        }
        return '-';
    }
}
