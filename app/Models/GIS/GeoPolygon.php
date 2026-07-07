<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoPolygon extends Model {
    use HasFactory;

    protected $table = 'gis_geo_polygons';

    protected $fillable = [
        'uuid',
        'vertices',
        'area_meters_squared',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'vertices' => 'array',
        'metadata' => 'array',
        'area_meters_squared' => 'float',
    ];
}
