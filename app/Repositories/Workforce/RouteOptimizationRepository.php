<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\RouteOptimization as RouteOptimizationModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\RouteOptimization;
use Src\Domain\Workforce\Repositories\RouteOptimizationRepositoryInterface;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class RouteOptimizationRepository extends BaseRepository implements RouteOptimizationRepositoryInterface
{
    public function __construct(RouteOptimizationModel $model)
    {
        parent::__construct($model);
    }

    public function save(RouteOptimization $routeOptimization): RouteOptimization
    {
        $this->model->updateOrCreate(
            ['uuid' => $routeOptimization->id->value],
            [
                'technician_id' => $routeOptimization->technicianId->value,
                'start_latitude' => $routeOptimization->startLocation->latitude,
                'start_longitude' => $routeOptimization->startLocation->longitude,
                'end_latitude' => $routeOptimization->endLocation->latitude,
                'end_longitude' => $routeOptimization->endLocation->longitude,
                'stops' => array_map(fn($stop) => $stop->toArray(), $routeOptimization->stops),
                'total_distance' => $routeOptimization->totalDistance,
                'total_time' => $routeOptimization->totalTime,
                'optimization_method' => $routeOptimization->optimizationMethod,
                'calculated_at' => $routeOptimization->calculatedAt,
            ]
        );

        return $routeOptimization;
    }

    public function findById(Uuid $id): ?RouteOptimization
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $stops = array_map(fn($s) => new GPSCoordinate($s['latitude'], $s['longitude']), $model->stops ?? []);

        return new RouteOptimization(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            new GPSCoordinate($model->start_latitude, $model->start_longitude),
            new GPSCoordinate($model->end_latitude, $model->end_longitude),
            $stops,
            $model->total_distance,
            $model->total_time,
            $model->optimization_method,
            $model->calculated_at->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable()
        );
    }
}
