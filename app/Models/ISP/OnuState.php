<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnuState extends Model
{
    use HasFactory;

    protected $fillable = [
        'onu_id',
        'desired_state',
        'actual_state',
        'state_hash',
        'actual_hash',
        'drift_status',
        'last_verified_at',
    ];

    protected $casts = [
        'desired_state' => 'array',
        'actual_state' => 'array',
        'last_verified_at' => 'datetime',
    ];

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }
}
