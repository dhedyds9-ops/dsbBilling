<?php

namespace App\Services\Workforce;

use Illuminate\Support\Facades\DB;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCInspection;
use Src\Domain\Workforce\QCChecklist;
use Src\Domain\Workforce\QCResult;
use Src\Domain\Workforce\QCApproval;
use Src\Domain\Workforce\Enums\QCStatus;
use Src\Domain\Workforce\Enums\QCResultStatus;
use Src\Domain\Workforce\Enums\QCApprovalStatus;
use Src\Domain\Workforce\Repositories\QCInspectionRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCChecklistRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCResultRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCApprovalRepositoryInterface;
use Src\Domain\Workforce\Events\QCPassedEvent;
use Src\Domain\Workforce\Events\QCFailedEvent;
use Src\Domain\Workforce\Events\ActivationApprovedEvent;

class QCService
{
    public function __construct(
        protected QCInspectionRepositoryInterface $inspectionRepository,
        protected QCChecklistRepositoryInterface $checklistRepository,
        protected QCResultRepositoryInterface $resultRepository,
        protected QCApprovalRepositoryInterface $approvalRepository,
    ) {}

    public function createInspection(Uuid $taskId, Uuid $inspectorId): QCInspection
    {
        $inspection = QCInspection::create($taskId, $inspectorId);
        $this->inspectionRepository->save($inspection);

        $defaultChecklists = [
            'Verifikasi peralatan',
            'Instalasi kabel',
            'Konfigurasi perangkat',
            'Tes koneksi',
            'Dokumentasi akhir'
        ];

        foreach ($defaultChecklists as $item) {
            $checklist = QCChecklist::create($inspection->id, $item);
            $this->checklistRepository->save($checklist);
        }

        return $inspection;
    }

    public function startInspection(Uuid $inspectionId): QCInspection
    {
        $inspection = $this->inspectionRepository->findById($inspectionId);
        if (!$inspection) {
            throw new \InvalidArgumentException('Inspection not found');
        }

        $inspection->start();
        $this->inspectionRepository->save($inspection);

        return $inspection;
    }

    public function passInspection(Uuid $inspectionId, ?string $notes = null): QCInspection
    {
        $inspection = $this->inspectionRepository->findById($inspectionId);
        if (!$inspection) {
            throw new \InvalidArgumentException('Inspection not found');
        }

        if ($notes) {
            $inspection->addNotes($notes);
        }

        $inspection->pass();
        $this->inspectionRepository->save($inspection);

        event(QCPassedEvent::create($inspection->id, $inspection->taskId));

        return $inspection;
    }

    public function failInspection(Uuid $inspectionId, ?string $notes = null): QCInspection
    {
        $inspection = $this->inspectionRepository->findById($inspectionId);
        if (!$inspection) {
            throw new \InvalidArgumentException('Inspection not found');
        }

        if ($notes) {
            $inspection->addNotes($notes);
        }

        $inspection->fail();
        $this->inspectionRepository->save($inspection);

        event(QCFailedEvent::create($inspection->id, $inspection->taskId, $inspection->notes));

        return $inspection;
    }

    public function recordChecklistResult(Uuid $checklistId, QCResultStatus $status, ?string $notes = null): QCResult
    {
        $existingResult = $this->resultRepository->findByChecklistId($checklistId);
        if ($existingResult) {
            $existingResult->updateStatus($status);
            if ($notes) {
                $existingResult->addNotes($notes);
            }
            $this->resultRepository->save($existingResult);

            return $existingResult;
        }

        $result = QCResult::create($checklistId, $status, $notes);
        $this->resultRepository->save($result);

        return $result;
    }

    public function createApproval(Uuid $inspectionId, Uuid $approverId): QCApproval
    {
        $approval = QCApproval::create($inspectionId, $approverId);
        $this->approvalRepository->save($approval);

        return $approval;
    }

    public function approveActivation(Uuid $approvalId, ?string $notes = null): QCApproval
    {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            throw new \InvalidArgumentException('Approval not found');
        }

        if ($notes) {
            $approval->addNotes($notes);
        }

        $approval->approve();
        $this->approvalRepository->save($approval);

        $inspection = $this->inspectionRepository->findById($approval->inspectionId);
        event(ActivationApprovedEvent::create($approval->id, $inspection->id, $inspection->taskId));

        return $approval;
    }

    public function rejectApproval(Uuid $approvalId, ?string $notes = null): QCApproval
    {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            throw new \InvalidArgumentException('Approval not found');
        }

        if ($notes) {
            $approval->addNotes($notes);
        }

        $approval->reject();
        $this->approvalRepository->save($approval);

        return $approval;
    }
}
