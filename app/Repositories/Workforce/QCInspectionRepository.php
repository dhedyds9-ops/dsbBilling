<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\QCInspection as QCInspectionModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCInspection;
use Src\Domain\Workforce\Enums\QCStatus;
use Src\Domain\Workforce\Repositories\QCInspectionRepositoryInterface;

class QCInspectionRepository extends BaseRepository implements QCInspectionRepositoryInterface
{
    public function __construct(QCInspectionModel $model)
    {
        parent::__construct($model);
    }

    public function save(QCInspection $inspection): QCInspection
    {
        $this->model->updateOrCreate(
            ['uuid' => $inspection->id->value],
            [
                'task_id' => $inspection->taskId->value,
                'inspector_id' => $inspection->inspectorId->value,
                'status' => $inspection->status,
                'notes' => $inspection->notes,
                'inspected_at' => $inspection->inspectedAt,
            ]
        );

        return $inspection;
    }

    public function findById(Uuid $id): ?QCInspection
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new QCInspection(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->task_id),
            Uuid::fromString($model->inspector_id),
            $model->status,
            $model->notes,
            $model->inspected_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByTaskId(Uuid $taskId): ?QCInspection
    {
        $model = $this->model->where('task_id', $taskId->value)->first();
        if (!$model) {
            return null;
        }

        return new QCInspection(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->task_id),
            Uuid::fromString($model->inspector_id),
            $model->status,
            $model->notes,
            $model->inspected_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByInspectorId(Uuid $inspectorId): array
    {
        $models = $this->model->where('inspector_id', $inspectorId->value)
            ->orderBy('created_at')
            ->get();

        return $models->map(function ($model) {
            return new QCInspection(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->task_id),
                Uuid::fromString($model->inspector_id),
                $model->status,
                $model->notes,
                $model->inspected_at?->toDateTimeImmutable(),
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }
}
