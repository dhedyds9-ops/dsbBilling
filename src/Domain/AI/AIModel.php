<?php

namespace Src\Domain\AI;

use Src\Domain\AI\Enums\AIModelType;
use Src\Domain\AI\Enums\AIProvider;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AIModel extends AggregateRoot
{
    private array $config = [];
    private array $metrics = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly AIModelType $type,
        public readonly AIProvider $provider,
        public readonly string $version,
        public readonly ?string $description = null,
        public readonly ?Uuid $createdBy = null,
        public readonly bool $isActive = true,
        public readonly ?DateTimeImmutable $trainedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $name,
        AIModelType $type,
        AIProvider $provider,
        string $version,
        ?string $description = null,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            type: $type,
            provider: $provider,
            version: $version,
            description: $description,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsTrained(): void
    {
        $this->trainedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function recordMetrics(array $metrics): void
    {
        $this->metrics = array_merge($this->metrics, [
            'recorded_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ...$metrics,
        ]);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getAccuracy(): ?float
    {
        return $this->metrics['accuracy'] ?? null;
    }

    public function getPrecision(): ?float
    {
        return $this->metrics['precision'] ?? null;
    }

    public function getRecall(): ?float
    {
        return $this->metrics['recall'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'provider' => $this->provider->value,
            'provider_label' => $this->provider->getLabel(),
            'version' => $this->version,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'config' => $this->config,
            'metrics' => $this->metrics,
            'trained_at' => $this->trainedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
