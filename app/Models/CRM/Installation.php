<?php

namespace App\Models\CRM;

use App\Models\Customer\Contract;
use App\Models\ISP\Onu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'survey_id',
        'contract_id',
        'onu_id',
        'assigned_to',
        'scheduled_at',
        'completed_at',
        'status',
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
        'completed_at' => 'datetime',
        'photos' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
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
}
