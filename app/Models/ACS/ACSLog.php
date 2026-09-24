<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ACSLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_logs';

    protected $fillable = [
        'uuid',
        'acs_device_id',
        'type',
        'message',
        'details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(ACSDevice::class, 'acs_device_id');
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
