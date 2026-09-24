<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteOptimization extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'technician_id',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'stops',
        'total_distance',
        'total_time',
        'optimization_method',
        'calculated_at',
    ];

    protected $casts = [
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
        'stops' => 'array',
        'total_distance' => 'float',
        'total_time' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
