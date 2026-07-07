<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoRoute extends Model {
    use HasFactory;

    protected $table = 'gis_geo_routes';

    protected $fillable = [
        'uuid',
        'points',
        'total_distance_meters',
        'total_time_seconds',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'points' => 'array',
        'metadata' => 'array',
        'total_distance_meters' => 'float',
        'total_time_seconds' => 'integer',
    ];
}
