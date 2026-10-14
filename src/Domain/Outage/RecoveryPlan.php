<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\RecoveryStatus;
use Src\Domain\Outage\Events\RecoveryStarted;
use Src\Domain\Outage\Events\RecoveryFinished;
use Src\Domain\Outage\ValueObjects\RecoveryTimeEstimate;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RecoveryPlan extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $outageId,
        public readonly string $title,
        public readonly string $description,
        public RecoveryStatus $status,
        public RecoveryTimeEstimate $estimatedRecovery,
        public ?string $technicianId = null,
        public ?string $technicianName = null,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?int $actualDurationMinutes = null,
        public array $steps = [],
        public array $resources = [],
        public array $checkpoints = [],
        public array $actions = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $outageId,
        string $title,
        string $description,
        RecoveryTimeEstimate $estimatedRecovery,
        array $steps = []
    ): self {
        return new self(
            $id,
            $outageId,
            $title,
            $description,
            RecoveryStatus::PENDING,
            $estimatedRecovery,
            null,
            null,
            null,
            null,
            null,
            $steps
        );
    }

    public function assignTechnician(string $technicianId, ?string $technicianName = null): void
    {
        $this->technicianId = $technicianId;
        $this->technicianName = $technicianName;
        $this->metadata['assigned_at'] = new DateTimeImmutable();
    }

    public function start(): void
    {
        if ($this->status !== RecoveryStatus::PENDING) {
            throw new \InvalidArgumentException("Recovery plan is not in pending status");
        }

        $this->status = RecoveryStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();

        $this->recordThat(new RecoveryStarted(
            $this->outageId->value,
            $this->id->value,
            $this->technicianId ?? '',
            $this->technicianName,
            $this->estimatedRecovery->averageMinutes,
            $this->estimatedRecovery->method
        ));
    }

    public function addAction(string $action, string $performedBy, ?string $result = null): void
    {
        $this->actions[] = [
            'timestamp' => new DateTimeImmutable(),
            'action' => $action,
            'performed_by' => $performedBy,
            'result' => $result
        ];
    }

    public function completeStep(int $stepIndex, string $result): void
    {
        if (!isset($this->steps[$stepIndex])) {
            throw new \InvalidArgumentException("Step {$stepIndex} does not exist");
        }

        $this->steps[$stepIndex]['completed_at'] = new DateTimeImmutable();
        $this->steps[$stepIndex]['result'] = $result;
        $this->steps[$stepIndex]['status'] = 'completed';

        $this->addAction(
            "Completed step: {$this->steps[$stepIndex]['title']}",
            $this->technicianId ?? 'system',
            $result
        );
    }

    public function failStep(int $stepIndex, string $reason): void
    {
        if (!isset($this->steps[$stepIndex])) {
            throw new \InvalidArgumentException("Step {$stepIndex} does not exist");
        }

        $this->steps[$stepIndex]['failed_at'] = new DateTimeImmutable();
        $this->steps[$stepIndex]['failure_reason'] = $reason;
        $this->steps[$stepIndex]['status'] = 'failed';

        $this->addAction(
            "Failed step: {$this->steps[$stepIndex]['title']}",
            $this->technicianId ?? 'system',
            "Failed: {$reason}"
        );
    }

    public function addCheckpoint(string $name, string $description): void
    {
        $this->checkpoints[] = [
            'name' => $name,
            'description' => $description,
            'created_at' => new DateTimeImmutable(),
            'passed' => false
        ];
    }

    public function passCheckpoint(int $checkpointIndex, string $notes = ''): void
    {
        if (!isset($this->checkpoints[$checkpointIndex])) {
            throw new \InvalidArgumentException("Checkpoint {$checkpointIndex} does not exist");
        }

        $this->checkpoints[$checkpointIndex]['passed'] = true;
        $this->checkpoints[$checkpointIndex]['passed_at'] = new DateTimeImmutable();
        $this->checkpoints[$checkpointIndex]['notes'] = $notes;
    }

    public function verify(): void
    {
        if ($this->status !== RecoveryStatus::IN_PROGRESS) {
            throw new \InvalidArgumentException("Cannot verify recovery that is not in progress");
        }

        $this->status = RecoveryStatus::VERIFICATION;
    }

    public function complete(bool $successful = true, ?string $notes = null): void
    {
        if ($this->status !== RecoveryStatus::IN_PROGRESS && $this->status !== RecoveryStatus::VERIFICATION) {
            throw new \InvalidArgumentException("Cannot complete recovery in current status");
        }

        $this->completedAt = new DateTimeImmutable();
        $this->status = $successful ? RecoveryStatus::COMPLETED : RecoveryStatus::FAILED;
        
        if ($this->startedAt) {
            $this->actualDurationMinutes = (int) (
                ($this->completedAt->getTimestamp() - $this->startedAt->getTimestamp()) / 60
            );
        }

        $this->recordThat(new RecoveryFinished(
            $this->outageId->value,
            $this->id->value,
            $successful,
            $this->actualDurationMinutes ?? 0,
            0,
            0,
            $notes
        ));
    }

    public function cancel(string $reason): void
    {
        $this->status = RecoveryStatus::CANCELLED;
        $this->metadata['cancelled_at'] = new DateTimeImmutable();
        $this->metadata['cancellation_reason'] = $reason;
    }

    public function getProgressPercentage(): float
    {
        if (empty($this->steps)) {
            return 0.0;
        }

        $completed = count(array_filter($this->steps, fn($s) => ($s['status'] ?? '') === 'completed'));
        return ($completed / count($this->steps)) * 100;
    }

    public function isWithinEstimate(): bool
    {
        if ($this->actualDurationMinutes === null || $this->startedAt === null) {
            return true;
        }

        return $this->actualDurationMinutes <= $this->estimatedRecovery->maximumMinutes;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
