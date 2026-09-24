<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisionQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_provision_queues';

    protected $fillable = [
        'uuid',
        'acs_device_id',
        'provision_profile_id',
        'provision_template_id',
        'status',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ACSDevice::class);
    }

    public function profile()
    {
        return $this->belongsTo(ProvisionProfile::class);
    }

    public function template()
    {
        return $this->belongsTo(ProvisionTemplate::class);
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
