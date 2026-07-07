<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\Enums\MapLayerType;
use Src\Domain\GIS\Enums\CoordinateSystem;

class MapLayer extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public MapLayerType $type,
        public readonly ?string $sourceUrl = null,
        public readonly ?array $layerOptions = null,
        public readonly bool $isVisible = true,
        public readonly int $orderIndex = 0,
        public CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
        public ?string $description = null,
        public ?array $metadata = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        string $name,
        MapLayerType $type,
        ?string $sourceUrl = null,
        ?array $layerOptions = null,
        bool $isVisible = true,
        int $orderIndex = 0,
        ?string $description = null,
        ?array $metadata = null,
        CoordinateSystem $coordinateSystem = CoordinateSystem::WGS84,
    ): self {
        return new self(
            Uuid::random(),
            $name,
            $type,
            $sourceUrl,
            $layerOptions,
            $isVisible,
            $orderIndex,
            $coordinateSystem,
            $description,
            $metadata,
        );
    }

    public function toggleVisibility(): void {
        $this->isVisible = !$this->isVisible;
        $this->updatedAt = new DateTimeImmutable();
    }
}
