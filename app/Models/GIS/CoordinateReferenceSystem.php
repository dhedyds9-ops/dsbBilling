<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\CoordinateSystem;

class CoordinateReferenceSystem extends Model {
    use HasFactory;

    protected $table = 'gis_coordinate_reference_systems';

    protected $fillable = [
        'uuid',
        'system',
        'srs_name',
        'parameters',
        'description',
        'metadata',
    ];

    protected $casts = [
        'system' => CoordinateSystem::class,
        'parameters' => 'array',
        'metadata' => 'array',
    ];
}
