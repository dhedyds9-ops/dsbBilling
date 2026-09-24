<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\Geofence as GeofenceModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Geofence;
use Src\Domain\Workforce\Enums\GeofenceType;
use Src\Domain\Workforce\Repositories\GeofenceRepositoryInterface;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class GeofenceRepository extends BaseRepository implements GeofenceRepositoryInterface
{
    public function __construct(GeofenceModel $model)
    {
        parent::__construct($model);
    }

    public function save(Geofence $geofence): Geofence
    {
        $this->model->updateOrCreate(
            ['uuid' => $geofence->id->value],
            [
                'name' => $geofence->name,
                'type' => $geofence->type,
                'center_latitude' => $geofence->center->latitude,
                'center_longitude' => $geofence->center->longitude,
                'radius' => $geofence->radius,
                'vertices' => $geofence->vertices,
                'description' => $geofence->description,
                'is_active' => $geofence->isActive,
            ]
        );

        return $geofence;
    }

    public function findById(Uuid $id): ?Geofence
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $vertices = array_map(fn($v) => new GPSCoordinate($v['latitude'], $v['longitude']), $model->vertices ?? []);

        return new Geofence(
            Uuid::fromString($model->uuid),
            $model->name,
            $model->type,
            new GPSCoordinate($model->center_latitude, $model->center_longitude),
            $model->radius,
            $vertices,
            $model->description,
            $model->is_active,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findAllActive(): array
    {
        $models = $this->model->where('is_active', true)->get();

        return $models->map(function ($model) {
            $vertices = array_map(fn($v) => new GPSCoordinate($v['latitude'], $v['longitude']), $model->vertices ?? []);

            return new Geofence(
                Uuid::fromString($model->uuid),
                $model->name,
                $model->type,
                new GPSCoordinate($model->center_latitude, $model->center_longitude),
                $model->radius,
                $vertices,
                $model->description,
                $model->is_active,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }
}
