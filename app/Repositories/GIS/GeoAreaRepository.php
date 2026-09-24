<?php

namespace App\Repositories\GIS;

use App\Models\GIS\GeoArea as GeoAreaModel;
use App\Models\GIS\GeoPolygon as GeoPolygonModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoArea;
use Src\Domain\GIS\GeoPolygon;
use Src\Domain\GIS\Repositories\GeoAreaRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoBoundingBox;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoAreaRepository extends BaseRepository implements GeoAreaRepositoryInterface
{
    public function __construct(GeoAreaModel $model)
    {
        parent::__construct($model);
    }

    private function hydratePolygon(string $polygonUuid): GeoPolygon
    {
        $polygonModel = GeoPolygonModel::where('uuid', $polygonUuid)->first();
        $vertices = array_map(fn($v) => new Coordinate(
            new Latitude($v['latitude']),
            new Longitude($v['longitude'])
        ), $polygonModel->vertices);

        return new GeoPolygon(
            Uuid::fromString($polygonModel->uuid),
            $vertices,
            $polygonModel->area_meters_squared,
            $polygonModel->coordinate_system,
            $polygonModel->name,
            $polygonModel->description,
            $polygonModel->metadata,
            $polygonModel->created_at?->toDateTimeImmutable(),
            $polygonModel->updated_at?->toDateTimeImmutable(),
        );
    }

    public function save(GeoArea $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'geo_polygon_uuid' => $entity->polygon->id->value,
                'area_meters_squared' => $entity->areaMetersSquared,
                'bounding_box' => $entity->boundingBox?->toArray(),
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?GeoArea
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $polygon = $this->hydratePolygon($model->geo_polygon_uuid);
        $boundingBox = $model->bounding_box ? GeoBoundingBox::fromCoordinates(
            $model->bounding_box['southWest']['latitude'],
            $model->bounding_box['southWest']['longitude'],
            $model->bounding_box['northEast']['latitude'],
            $model->bounding_box['northEast']['longitude']
        ) : null;

        return new GeoArea(
            Uuid::fromString($model->uuid),
            $polygon,
            $model->area_meters_squared,
            $boundingBox,
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
            $polygon = $this->hydratePolygon($model->geo_polygon_uuid);
            $boundingBox = $model->bounding_box ? GeoBoundingBox::fromCoordinates(
                $model->bounding_box['southWest']['latitude'],
                $model->bounding_box['southWest']['longitude'],
                $model->bounding_box['northEast']['latitude'],
                $model->bounding_box['northEast']['longitude']
            ) : null;

            return new GeoArea(
                Uuid::fromString($model->uuid),
                $polygon,
                $model->area_meters_squared,
                $boundingBox,
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
