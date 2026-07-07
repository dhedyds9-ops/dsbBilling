<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\QCApproval as QCApprovalModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCApproval;
use Src\Domain\Workforce\Enums\QCApprovalStatus;
use Src\Domain\Workforce\Repositories\QCApprovalRepositoryInterface;

class QCApprovalRepository extends BaseRepository implements QCApprovalRepositoryInterface
{
    public function __construct(QCApprovalModel $model)
    {
        parent::__construct($model);
    }

    public function save(QCApproval $approval): QCApproval
    {
        $this->model->updateOrCreate(
            ['uuid' => $approval->id->value],
            [
                'inspection_id' => $approval->inspectionId->value,
                'approver_id' => $approval->approverId->value,
                'status' => $approval->status,
                'notes' => $approval->notes,
                'approved_at' => $approval->approvedAt,
            ]
        );

        return $approval;
    }

    public function findById(Uuid $id): ?QCApproval
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new QCApproval(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->inspection_id),
            Uuid::fromString($model->approver_id),
            $model->status,
            $model->notes,
            $model->approved_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByInspectionId(Uuid $inspectionId): ?QCApproval
    {
        $model = $this->model->where('inspection_id', $inspectionId->value)->first();
        if (!$model) {
            return null;
        }

        return new QCApproval(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->inspection_id),
            Uuid::fromString($model->approver_id),
            $model->status,
            $model->notes,
            $model->approved_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }
}
