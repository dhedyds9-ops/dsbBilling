<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\GPSHistory as GPSHistoryModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\GPSHistory;
use Src\Domain\Workforce\Repositories\GPSHistoryRepositoryInterface;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class GPSHistoryRepository extends BaseRepository implements GPSHistoryRepositoryInterface
{
    public function __construct(GPSHistoryModel $model)
    {
        parent::__construct($model);
    }

    public function save(GPSHistory $gpsHistory): GPSHistory
    {
        $this->model->updateOrCreate(
            ['uuid' => $gpsHistory->id->value],
            [
                'technician_id' => $gpsHistory->technicianId->value,
                'latitude' => $gpsHistory->coordinate->latitude,
                'longitude' => $gpsHistory->coordinate->longitude,
                'speed' => $gpsHistory->speed,
                'heading' => $gpsHistory->heading,
                'altitude' => $gpsHistory->altitude,
                'logged_at' => $gpsHistory->loggedAt,
            ]
        );

        return $gpsHistory;
    }

    public function findById(Uuid $id): ?GPSHistory
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new GPSHistory(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            new GPSCoordinate($model->latitude, $model->longitude),
            $model->speed,
            $model->heading,
            $model->altitude,
            $model->logged_at->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable()
        );
    }

    public function findByTechnicianId(Uuid $technicianId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array
    {
        $query = $this->model->where('technician_id', $technicianId->value);

        if ($startDate) {
            $query->where('logged_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('logged_at', '<=', $endDate);
        }

        $models = $query->orderBy('logged_at')->get();

        return $models->map(function ($model) {
            return new GPSHistory(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->technician_id),
                new GPSCoordinate($model->latitude, $model->longitude),
                $model->speed,
                $model->heading,
                $model->altitude,
                $model->logged_at->toDateTimeImmutable(),
                $model->created_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function findLatestByTechnicianId(Uuid $technicianId): ?GPSHistory
    {
        $model = $this->model->where('technician_id', $technicianId->value)->latest('logged_at')->first();
        if (!$model) {
            return null;
        }

        return new GPSHistory(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            new GPSCoordinate($model->latitude, $model->longitude),
            $model->speed,
            $model->heading,
            $model->altitude,
            $model->logged_at->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable()
        );
    }
}
