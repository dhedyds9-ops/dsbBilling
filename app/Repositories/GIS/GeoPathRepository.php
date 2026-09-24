<?php

namespace App\Repositories\GIS;

use App\Models\GIS\GeoPath as GeoPathModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\GeoPath;
use Src\Domain\GIS\Repositories\GeoPathRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\GIS\ValueObjects\Latitude;
use Src\Domain\GIS\ValueObjects\Longitude;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class GeoPathRepository extends BaseRepository implements GeoPathRepositoryInterface
{
    public function __construct(GeoPathModel $model)
    {
        parent::__construct($model);
    }

    public function save(GeoPath $entity): void
    {
        $paths = array_map(function (array $path) {
            return array_map(fn(Coordinate $c) => [
                'latitude' => $c->latitude->value,
                'longitude' => $c->longitude->value
            ], $path);
        }, $entity->paths);

        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'paths' => $paths,
                'coordinate_system' => $entity->coordinateSystem->value,
                'name' => $entity->name,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?GeoPath
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $paths = array_map(function (array $path) {
            return array_map(fn($p) => new Coordinate(
                new Latitude($p['latitude']),
                new Longitude($p['longitude'])
            ), $path);
        }, $model->paths);

        return new GeoPath(
            Uuid::fromString($model->uuid),
            $paths,
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
            $paths = array_map(function (array $path) {
                return array_map(fn($p) => new Coordinate(
                    new Latitude($p['latitude']),
                    new Longitude($p['longitude'])
                ), $path);
            }, $model->paths);

            return new GeoPath(
                Uuid::fromString($model->uuid),
                $paths,
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
