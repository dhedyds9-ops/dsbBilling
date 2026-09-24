<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\SyncConflict as SyncConflictModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncConflict;
use Src\Domain\Workforce\Enums\SyncConflictResolutionType;
use Src\Domain\Workforce\Repositories\SyncConflictRepositoryInterface;

class SyncConflictRepository extends BaseRepository implements SyncConflictRepositoryInterface
{
    public function __construct(SyncConflictModel $model)
    {
        parent::__construct($model);
    }

    public function save(SyncConflict $conflict): SyncConflict
    {
        $this->model->updateOrCreate(
            ['uuid' => $conflict->id->value],
            [
                'task_id' => $conflict->taskId->value,
                'user_id' => $conflict->userId->value,
                'model_type' => $conflict->modelType,
                'model_id' => $conflict->modelId->value,
                'server_data' => $conflict->serverData,
                'client_data' => $conflict->clientData,
                'resolution_type' => $conflict->resolutionType,
                'resolved_data' => $conflict->resolvedData,
                'is_resolved' => $conflict->isResolved,
                'resolved_by' => $conflict->resolvedBy?->value,
                'resolved_at' => $conflict->resolvedAt,
            ]
        );

        return $conflict;
    }

    public function findById(Uuid $id): ?SyncConflict
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new SyncConflict(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->task_id),
            Uuid::fromString($model->user_id),
            $model->model_type,
            Uuid::fromString($model->model_id),
            $model->server_data,
            $model->client_data,
            $model->resolution_type,
            $model->resolved_data,
            $model->is_resolved,
            $model->resolved_by ? Uuid::fromString($model->resolved_by) : null,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
            $model->resolved_at?->toDateTimeImmutable()
        );
    }

    public function findByUserId(Uuid $userId): array
    {
        $models = $this->model->where('user_id', $userId->value)->orderBy('created_at')->get();

        return $models->map(function ($model) {
            return new SyncConflict(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->task_id),
                Uuid::fromString($model->user_id),
                $model->model_type,
                Uuid::fromString($model->model_id),
                $model->server_data,
                $model->client_data,
                $model->resolution_type,
                $model->resolved_data,
                $model->is_resolved,
                $model->resolved_by ? Uuid::fromString($model->resolved_by) : null,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable(),
                $model->resolved_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function findUnresolvedByUserId(Uuid $userId): array
    {
        $models = $this->model->where('user_id', $userId->value)
            ->where('is_resolved', false)
            ->orderBy('created_at')
            ->get();

        return $models->map(function ($model) {
            return new SyncConflict(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->task_id),
                Uuid::fromString($model->user_id),
                $model->model_type,
                Uuid::fromString($model->model_id),
                $model->server_data,
                $model->client_data,
                $model->resolution_type,
                $model->resolved_data,
                $model->is_resolved,
                $model->resolved_by ? Uuid::fromString($model->resolved_by) : null,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable(),
                $model->resolved_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
