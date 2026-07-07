<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class ServiceArea extends Model {
    use HasFactory;

    protected $table = 'gis_service_areas';

    protected $fillable = [
        'uuid',
        'geo_point_uuid',
        'radius_meters',
        'geo_area_uuid',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'metadata' => 'array',
        'radius_meters' => 'float',
    ];

    public function centerPoint() {
        return $this->belongsTo(GeoPoint::class, 'geo_point_uuid', 'uuid');
    }

    public function serviceArea() {
        return $this->belongsTo(GeoArea::class, 'geo_area_uuid', 'uuid');
    }
}
