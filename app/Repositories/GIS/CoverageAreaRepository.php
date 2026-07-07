<?php

namespace App\Repositories\GIS;

use App\Models\GIS\CoverageArea as CoverageAreaModel;
use App\Models\GIS\GeoArea as GeoAreaModel;
use App\Models\GIS\GeoPolygon as GeoPolygonModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\CoverageArea;
use Src\Domain\GIS\GeoArea;
use Src\Domain\GIS\GeoPolygon;
use Src\Domain\GIS\Repositories\CoverageAreaRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoBoundingBox;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageAreaRepository extends BaseRepository implements CoverageAreaRepositoryInterface
{
    public function __construct(CoverageAreaModel $model)
    {
        parent::__construct($model);
    }

    private function hydrateGeoArea(string $areaUuid): GeoArea
    {
        $areaModel = GeoAreaModel::where('uuid', $areaUuid)->first();
        $polygonModel = GeoPolygonModel::where('uuid', $areaModel->geo_polygon_uuid)->first();

        $vertices = array_map(fn($v) => new Coordinate(
            new Latitude($v['latitude']),
            new Longitude($v['longitude'])
        ), $polygonModel->vertices);

        $polygon = new GeoPolygon(
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

        $boundingBox = $areaModel->bounding_box ? GeoBoundingBox::fromCoordinates(
            $areaModel->bounding_box['southWest']['latitude'],
            $areaModel->bounding_box['southWest']['longitude'],
            $areaModel->bounding_box['northEast']['latitude'],
            $areaModel->bounding_box['northEast']['longitude']
        ) : null;

        return new GeoArea(
            Uuid::fromString($areaModel->uuid),
            $polygon,
            $areaModel->area_meters_squared,
            $boundingBox,
            $areaModel->coordinate_system,
            $areaModel->name,
            $areaModel->description,
            $areaModel->metadata,
            $areaModel->created_at?->toDateTimeImmutable(),
            $areaModel->updated_at?->toDateTimeImmutable(),
        );
    }

    public function save(CoverageArea $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'geo_area_uuid' => $entity->area->id->value,
                'parent_uuid' => $entity->parentId?->value,
                'status' => $entity->status->value,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?CoverageArea
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $area = $this->hydrateGeoArea($model->geo_area_uuid);

        return new CoverageArea(
            Uuid::fromString($model->uuid),
            $area,
            $model->parent_uuid ? Uuid::fromString($model->parent_uuid) : null,
            $model->status,
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
            $area = $this->hydrateGeoArea($model->geo_area_uuid);

            return new CoverageArea(
                Uuid::fromString($model->uuid),
                $area,
                $model->parent_uuid ? Uuid::fromString($model->parent_uuid) : null,
                $model->status,
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
