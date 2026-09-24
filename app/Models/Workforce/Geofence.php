<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\GeofenceType;

class Geofence extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'center_latitude',
        'center_longitude',
        'radius',
        'vertices',
        'description',
        'is_active',
    ];

    protected $casts = [
        'type' => GeofenceType::class,
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'radius' => 'float',
        'vertices' => 'array',
        'is_active' => 'boolean',
    ];
}
