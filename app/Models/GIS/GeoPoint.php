<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoPoint extends Model {
    use HasFactory;

    protected $table = 'gis_geo_points';

    protected $fillable = [
        'uuid',
        'latitude',
        'longitude',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'metadata' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
