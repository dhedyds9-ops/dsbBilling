<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\RouteHistory as RouteHistoryModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\RouteHistory;
use Src\Domain\Workforce\Repositories\RouteHistoryRepositoryInterface;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class RouteHistoryRepository extends BaseRepository implements RouteHistoryRepositoryInterface
{
    public function __construct(RouteHistoryModel $model)
    {
        parent::__construct($model);
    }

    public function save(RouteHistory $routeHistory): RouteHistory
    {
        $this->model->updateOrCreate(
            ['uuid' => $routeHistory->id->value],
            [
                'technician_id' => $routeHistory->technicianId->value,
                'assignment_id' => $routeHistory->assignmentId->value,
                'start_latitude' => $routeHistory->startLocation->latitude,
                'start_longitude' => $routeHistory->startLocation->longitude,
                'end_latitude' => $routeHistory->endLocation->latitude,
                'end_longitude' => $routeHistory->endLocation->longitude,
                'distance' => $routeHistory->distance,
                'travel_time' => $routeHistory->travelTime,
                'started_at' => $routeHistory->startedAt,
                'ended_at' => $routeHistory->endedAt,
                'waypoints' => array_map(fn($wp) => $wp->toArray(), $routeHistory->waypoints),
            ]
        );

        return $routeHistory;
    }

    public function findById(Uuid $id): ?RouteHistory
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $waypoints = array_map(fn($wp) => new GPSCoordinate($wp['latitude'], $wp['longitude']), $model->waypoints ?? []);

        return new RouteHistory(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            Uuid::fromString($model->assignment_id),
            new GPSCoordinate($model->start_latitude, $model->start_longitude),
            new GPSCoordinate($model->end_latitude, $model->end_longitude),
            $model->distance,
            $model->travel_time,
            $model->started_at->toDateTimeImmutable(),
            $model->ended_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
            $waypoints
        );
    }
}
