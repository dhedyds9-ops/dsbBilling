<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\SyncTask as SyncTaskModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncTask;
use Src\Domain\Workforce\Enums\SyncTaskStatus;
use Src\Domain\Workforce\Enums\SyncTaskType;
use Src\Domain\Workforce\Repositories\SyncTaskRepositoryInterface;

class SyncTaskRepository extends BaseRepository implements SyncTaskRepositoryInterface
{
    public function __construct(SyncTaskModel $model)
    {
        parent::__construct($model);
    }

    public function save(SyncTask $task): SyncTask
    {
        $this->model->updateOrCreate(
            ['uuid' => $task->id->value],
            [
                'user_id' => $task->userId->value,
                'type' => $task->type,
                'status' => $task->status,
                'payload' => $task->payload,
                'model_type' => $task->modelType,
                'model_id' => $task->modelId?->value,
                'retry_count' => $task->retryCount,
                'last_retry_at' => $task->lastRetryAt,
                'error_message' => $task->errorMessage,
                'is_compressed' => $task->isCompressed,
            ]
        );

        return $task;
    }

    public function findById(Uuid $id): ?SyncTask
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new SyncTask(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->user_id),
            $model->type,
            $model->status,
            $model->payload,
            $model->model_type,
            $model->model_id ? Uuid::fromString($model->model_id) : null,
            $model->retry_count,
            $model->last_retry_at?->toDateTimeImmutable(),
            $model->error_message,
            $model->is_compressed,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByUserId(Uuid $userId): array
    {
        $models = $this->model->where('user_id', $userId->value)->orderBy('created_at')->get();

        return $models->map(function ($model) {
            return new SyncTask(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->user_id),
                $model->type,
                $model->status,
                $model->payload,
                $model->model_type,
                $model->model_id ? Uuid::fromString($model->model_id) : null,
                $model->retry_count,
                $model->last_retry_at?->toDateTimeImmutable(),
                $model->error_message,
                $model->is_compressed,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function findPendingByUserId(Uuid $userId): array
    {
        $models = $this->model->where('user_id', $userId->value)
            ->where('status', SyncTaskStatus::PENDING)
            ->orderBy('created_at')
            ->get();

        return $models->map(function ($model) {
            return new SyncTask(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->user_id),
                $model->type,
                $model->status,
                $model->payload,
                $model->model_type,
                $model->model_id ? Uuid::fromString($model->model_id) : null,
                $model->retry_count,
                $model->last_retry_at?->toDateTimeImmutable(),
                $model->error_message,
                $model->is_compressed,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function findFailedByUserId(Uuid $userId): array
    {
        $models = $this->model->where('user_id', $userId->value)
            ->where('status', SyncTaskStatus::FAILED)
            ->orderBy('created_at')
            ->get();

        return $models->map(function ($model) {
            return new SyncTask(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->user_id),
                $model->type,
                $model->status,
                $model->payload,
                $model->model_type,
                $model->model_id ? Uuid::fromString($model->model_id) : null,
                $model->retry_count,
                $model->last_retry_at?->toDateTimeImmutable(),
                $model->error_message,
                $model->is_compressed,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
