<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoverageStatus;
use Src\Domain\GIS\Enums\CoordinateSystem;

class CoverageArea extends Model {
    use HasFactory;

    protected $table = 'gis_coverage_areas';

    protected $fillable = [
        'uuid',
        'geo_area_uuid',
        'parent_uuid',
        'status',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'status' => CoverageStatus::class,
        'coordinate_system' => CoordinateSystem::class,
        'metadata' => 'array',
    ];

    public function area() {
        return $this->belongsTo(GeoArea::class, 'geo_area_uuid', 'uuid');
    }

    public function parent() {
        return $this->belongsTo(self::class, 'parent_uuid', 'uuid');
    }
}
