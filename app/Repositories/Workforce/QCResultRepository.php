<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\QCResult as QCResultModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCResult;
use Src\Domain\Workforce\Enums\QCResultStatus;
use Src\Domain\Workforce\Repositories\QCResultRepositoryInterface;

class QCResultRepository extends BaseRepository implements QCResultRepositoryInterface
{
    public function __construct(QCResultModel $model)
    {
        parent::__construct($model);
    }

    public function save(QCResult $result): QCResult
    {
        $this->model->updateOrCreate(
            ['uuid' => $result->id->value],
            [
                'checklist_id' => $result->checklistId->value,
                'status' => $result->status,
                'notes' => $result->notes,
            ]
        );

        return $result;
    }

    public function findById(Uuid $id): ?QCResult
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new QCResult(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->checklist_id),
            $model->status,
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByChecklistId(Uuid $checklistId): ?QCResult
    {
        $model = $this->model->where('checklist_id', $checklistId->value)->first();
        if (!$model) {
            return null;
        }

        return new QCResult(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->checklist_id),
            $model->status,
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
