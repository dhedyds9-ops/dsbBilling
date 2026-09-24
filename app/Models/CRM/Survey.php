<?php

namespace App\Models\CRM;

use App\Models\ISP\Odp;
use App\Models\ISP\Splitter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'prospect_id',
        'odp_id',
        'splitter_id',
        'assigned_to',
        'scheduled_at',
        'completed_at',
        'status',
        'latitude',
        'longitude',
        'address',
        'port',
        'distance',
        'cable_estimation',
        'material_estimation',
        'photos_location',
        'photos_odp',
        'notes',
        'recommendation',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'distance' => 'decimal:2',
        'cable_estimation' => 'decimal:2',
        'material_estimation' => 'array',
        'photos_location' => 'array',
        'photos_odp' => 'array',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function splitter()
    {
        return $this->belongsTo(Splitter::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function installation()
    {
        return $this->hasOne(Installation::class);
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
