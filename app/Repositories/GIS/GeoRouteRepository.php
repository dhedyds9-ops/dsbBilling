<?php

namespace App\Repositories\GIS;

use App\Models\GIS\GeoRoute as GeoRouteModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoRoute;
use Src\Domain\GIS\Repositories\GeoRouteRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\GeoDistance;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoRouteRepository extends BaseRepository implements GeoRouteRepositoryInterface
{
    public function __construct(GeoRouteModel $model)
    {
        parent::__construct($model);
    }

    public function save(GeoRoute $entity): void
    {
        $points = array_map(fn(Coordinate $c) => [
            'latitude' => $c->latitude->value,
            'longitude' => $c->longitude->value
        ], $entity->points);

        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'points' => $points,
                'total_distance_meters' => $entity->totalDistance->meters,
                'total_time_seconds' => $entity->totalTimeSeconds,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?GeoRoute
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $points = array_map(fn($p) => new Coordinate(
            new Latitude($p['latitude']),
            new Longitude($p['longitude'])
        ), $model->points);

        return new GeoRoute(
            Uuid::fromString($model->uuid),
            $points,
            GeoDistance::fromMeters($model->total_distance_meters),
            $model->total_time_seconds,
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
            $points = array_map(fn($p) => new Coordinate(
                new Latitude($p['latitude']),
                new Longitude($p['longitude'])
            ), $model->points);

            return new GeoRoute(
                Uuid::fromString($model->uuid),
                $points,
                GeoDistance::fromMeters($model->total_distance_meters),
                $model->total_time_seconds,
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
