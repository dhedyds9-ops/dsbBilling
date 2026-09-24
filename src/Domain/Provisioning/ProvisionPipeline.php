<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum PipelineStep: string
{
    case RESERVE_RESOURCES = 'reserve_resources';
    case ASSIGN_DEVICE = 'assign_device';
    case ASSIGN_VLAN = 'assign_vlan';
    case ASSIGN_IP = 'assign_ip';
    case ASSIGN_QUEUE = 'assign_queue';
    case PROVISION_ROUTER = 'provision_router';
    case PROVISION_RADIUS = 'provision_radius';
    case PROVISION_ONU = 'provision_onu';
    case VERIFY_PROVISIONING = 'verify_provisioning';
    case RELEASE_RESERVATION = 'release_reservation';
    case GENERATE_BILLING = 'generate_billing';
}

enum PipelineStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

class ProvisionPipeline extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public array $steps,
        public PipelineStatus $status = PipelineStatus::DRAFT,
        public ?string $description = null,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        Uuid $id,
        string $name,
        array $steps,
        ?string $description = null
    ): self {
        return new self(
            $id,
            $name,
            $steps,
            PipelineStatus::DRAFT,
            $description,
            new DateTimeImmutable()
        );
    }

    public function activate(): void
    {
        $this->status = PipelineStatus::ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status = PipelineStatus::INACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addStep(PipelineStep $step, int $order): void
    {
        $this->steps[] = [
            'step' => $step,
            'order' => $order
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getOrderedSteps(): array
    {
        usort($this->steps, fn($a, $b) => $a['order'] <=> $b['order']);
        return array_column($this->steps, 'step');
    }
}
