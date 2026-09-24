<?php

namespace Src\Domain\BusinessIntelligence;

use Src\Domain\BusinessIntelligence\Enums\DashboardStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Dashboard extends AggregateRoot
{
    private DashboardStatus $status;
    private array $widgets = [];
    private array $filters = [];
    private array $variables = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $module,
        public readonly ?Uuid $createdBy = null,
        public readonly int $version = 1,
        public readonly ?DateTimeImmutable $effectiveFrom = null,
        public readonly ?DateTimeImmutable $effectiveTo = null,
        public readonly ?array $metadata = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {
        $this->status = DashboardStatus::DRAFT;
    }

    public static function create(
        string $name,
        string $description,
        string $module,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            description: $description,
            module: $module,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function addWidget(Uuid $widgetId, int $positionX, int $positionY, int $width, int $height): void
    {
        $this->widgets[] = [
            'widget_id' => $widgetId->toString(),
            'position_x' => $positionX,
            'position_y' => $positionY,
            'width' => $width,
            'height' => $height,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeWidget(Uuid $widgetId): void
    {
        $this->widgets = array_filter(
            $this->widgets,
            fn($w) => $w['widget_id'] !== $widgetId->toString()
        );
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addFilter(string $field, string $operator, mixed $value): void
    {
        $this->filters[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setVariable(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function publish(): void
    {
        if ($this->status === DashboardStatus::ARCHIVED) {
            throw new \DomainException('Cannot publish archived dashboard');
        }
        $this->status = DashboardStatus::PUBLISHED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function archive(): void
    {
        $this->status = DashboardStatus::ARCHIVED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function share(): void
    {
        if ($this->status !== DashboardStatus::PUBLISHED) {
            throw new \DomainException('Only published dashboards can be shared');
        }
        $this->status = DashboardStatus::SHARED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStatus(): DashboardStatus
    {
        return $this->status;
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'description' => $this->description,
            'module' => $this->module,
            'status' => $this->status->value,
            'widgets' => $this->widgets,
            'filters' => $this->filters,
            'variables' => $this->variables,
            'version' => $this->version,
            'created_by' => $this->createdBy?->toString(),
            'effective_from' => $this->effectiveFrom?->format('Y-m-d'),
            'effective_to' => $this->effectiveTo?->format('Y-m-d'),
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
