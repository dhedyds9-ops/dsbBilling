<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoArea extends Model {
    use HasFactory;

    protected $table = 'gis_geo_areas';

    protected $fillable = [
        'uuid',
        'geo_polygon_uuid',
        'area_meters_squared',
        'bounding_box',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'bounding_box' => 'array',
        'metadata' => 'array',
        'area_meters_squared' => 'float',
    ];

    public function polygon() {
        return $this->belongsTo(GeoPolygon::class, 'geo_polygon_uuid', 'uuid');
    }
}
