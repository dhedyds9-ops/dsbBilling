<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RackUnit extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $rackId,
        public readonly int $position,
        public readonly int $size,
        public readonly bool $isOccupied = false,
        public readonly ?Uuid $assetId = null,
        public readonly ?string $description = null
    ) {}

    public static function create(
        Uuid $rackId,
        int $position,
        int $size = 1,
        ?string $description = null
    ): self {
        return new self(
            id: Uuid::generate(),
            rackId: $rackId,
            position: $position,
            size: $size,
            isOccupied: false,
            assetId: null,
            description: $description
        );
    }

    public function occupy(Uuid $assetId): void
    {
        if ($this->isOccupied) {
            throw new \DomainException("Rack unit is already occupied");
        }
        
        $this->isOccupied = true;
        $this->assetId = $assetId;
    }

    public function vacate(): void
    {
        $this->isOccupied = false;
        $this->assetId = null;
    }

    public function canAccommodate(int $requiredSize): bool
    {
        return !$this->isOccupied && $this->size >= $requiredSize;
    }
}
