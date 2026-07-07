<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'technician_id',
        'assignment_id',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'distance',
        'travel_time',
        'started_at',
        'ended_at',
        'waypoints',
    ];

    protected $casts = [
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
        'distance' => 'float',
        'travel_time' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'waypoints' => 'array',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
