<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class GeoPath extends Model {
    use HasFactory;

    protected $table = 'gis_geo_paths';

    protected $fillable = [
        'uuid',
        'paths',
        'coordinate_system',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'coordinate_system' => CoordinateSystem::class,
        'paths' => 'array',
        'metadata' => 'array',
    ];
}
