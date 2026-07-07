<?php

namespace App\Repositories\GIS;

use App\Models\GIS\GeoPolygon as GeoPolygonModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoPolygon;
use Src\Domain\GIS\Repositories\GeoPolygonRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoPolygonRepository extends BaseRepository implements GeoPolygonRepositoryInterface
{
    public function __construct(GeoPolygonModel $model)
    {
        parent::__construct($model);
    }

    public function save(GeoPolygon $entity): void
    {
        $vertices = array_map(fn(Coordinate $c) => [
            'latitude' => $c->latitude->value,
            'longitude' => $c->longitude->value
        ], $entity->vertices);

        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'vertices' => $vertices,
                'area_meters_squared' => $entity->areaMetersSquared,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?GeoPolygon
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $vertices = array_map(fn($v) => new Coordinate(
            new Latitude($v['latitude']),
            new Longitude($v['longitude'])
        ), $model->vertices);

        return new GeoPolygon(
            Uuid::fromString($model->uuid),
            $vertices,
            $model->area_meters_squared,
            $model->coordinate_system,
            $model->name,
            $model->description,
            $model->metadata,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }

    public function findAll(): array
    {
        return $this->model->all()->map(function ($model) {
            $vertices = array_map(fn($v) => new Coordinate(
                new Latitude($v['latitude']),
                new Longitude($v['longitude'])
            ), $model->vertices);

            return new GeoPolygon(
                Uuid::fromString($model->uuid),
                $vertices,
                $model->area_meters_squared,
                $model->coordinate_system,
                $model->name,
                $model->description,
                $model->metadata,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable(),
            );
        })->all();
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
