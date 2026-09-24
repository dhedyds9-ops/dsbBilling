<?php

namespace App\Repositories\GIS;

use App\Models\GIS\GeoPoint as GeoPointModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoPoint;
use Src\Domain\GIS\Repositories\GeoPointRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoPointRepository extends BaseRepository implements GeoPointRepositoryInterface
{
    public function __construct(GeoPointModel $model)
    {
        parent::__construct($model);
    }

    public function save(GeoPoint $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'latitude' => $entity->coordinate->latitude->value,
                'longitude' => $entity->coordinate->longitude->value,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?GeoPoint
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new GeoPoint(
            Uuid::fromString($model->uuid),
            new Coordinate(
                new Latitude($model->latitude),
                new Longitude($model->longitude)
            ),
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
            return new GeoPoint(
                Uuid::fromString($model->uuid),
                new Coordinate(
                    new Latitude($model->latitude),
                    new Longitude($model->longitude)
                ),
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
