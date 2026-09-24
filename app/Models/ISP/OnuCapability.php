<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnuCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'onu_id',
        'capabilities',
        'discovered_at',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'discovered_at' => 'datetime',
    ];

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }
}
