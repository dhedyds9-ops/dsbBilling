<?php

namespace App\Models\GIS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\GIS\Enums\MapLayerType;
use Src\Domain\GIS\Enums\CoordinateSystem;

class MapLayer extends Model {
    use HasFactory;

    protected $table = 'gis_map_layers';

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'source_url',
        'layer_options',
        'is_visible',
        'order_index',
        'coordinate_system',
        'description',
        'metadata',
    ];

    protected $casts = [
        'type' => MapLayerType::class,
        'coordinate_system' => CoordinateSystem::class,
        'layer_options' => 'array',
        'metadata' => 'array',
        'is_visible' => 'boolean',
        'order_index' => 'integer',
    ];
}
