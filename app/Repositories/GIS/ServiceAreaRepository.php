<?php

namespace App\Repositories\GIS;

use App\Models\GIS\ServiceArea as ServiceAreaModel;
use App\Models\GIS\GeoPoint as GeoPointModel;
use App\Models\GIS\GeoArea as GeoAreaModel;
use App\Models\GIS\GeoPolygon as GeoPolygonModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoArea;
use Src\Domain\GIS\GeoPoint;
use Src\Domain\GIS\GeoPolygon;
use Src\Domain\GIS\Repositories\ServiceAreaRepositoryInterface;
use Src\Domain\GIS\ServiceArea;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoBoundingBox;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ServiceAreaRepository extends BaseRepository implements ServiceAreaRepositoryInterface
{
    public function __construct(ServiceAreaModel $model)
    {
        parent::__construct($model);
    }

    private function hydrateGeoPoint(string $pointUuid): GeoPoint
    {
        $pointModel = GeoPointModel::where('uuid', $pointUuid)->first();
        return new GeoPoint(
            Uuid::fromString($pointModel->uuid),
            new Coordinate(
                new Latitude($pointModel->latitude),
                new Longitude($pointModel->longitude)
            ),
            $pointModel->coordinate_system,
            $pointModel->name,
            $pointModel->description,
            $pointModel->metadata,
            $pointModel->created_at?->toDateTimeImmutable(),
            $pointModel->updated_at?->toDateTimeImmutable(),
        );
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

    public function save(ServiceArea $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'geo_point_uuid' => $entity->centerPoint->id->value,
                'radius_meters' => $entity->radius->meters,
                'geo_area_uuid' => $entity->serviceArea?->id->value,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?ServiceArea
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $centerPoint = $this->hydrateGeoPoint($model->geo_point_uuid);
        $serviceArea = $model->geo_area_uuid ? $this->hydrateGeoArea($model->geo_area_uuid) : null;

        return new ServiceArea(
            Uuid::fromString($model->uuid),
            $centerPoint,
            GeoDistance::fromMeters($model->radius_meters),
            $serviceArea,
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
            $centerPoint = $this->hydrateGeoPoint($model->geo_point_uuid);
            $serviceArea = $model->geo_area_uuid ? $this->hydrateGeoArea($model->geo_area_uuid) : null;

            return new ServiceArea(
                Uuid::fromString($model->uuid),
                $centerPoint,
                GeoDistance::fromMeters($model->radius_meters),
                $serviceArea,
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
