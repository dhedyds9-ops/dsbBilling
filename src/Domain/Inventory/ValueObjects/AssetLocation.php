<?php

namespace Src\Domain\Inventory\ValueObjects;

use Src\Domain\SharedKernel\ValueObjects\Uuid;

readonly class AssetLocation
{
    public function __construct(
        public ?Uuid $warehouseId = null,
        public ?Uuid $rackId = null,
        public ?int $rackPosition = null, // For rack mount equipment
        public ?string $shelf = null,
        public ?string $bin = null,
        public ?string $description = null
    ) {}

    public static function inWarehouse(Uuid $warehouseId): self
    {
        return new self(warehouseId: $warehouseId);
    }

    public static function inRack(Uuid $warehouseId, Uuid $rackId, int $position): self
    {
        return new self(
            warehouseId: $warehouseId,
            rackId: $rackId,
            rackPosition: $position
        );
    }

    public static function installed(Uuid $warehouseId, string $description): self
    {
        return new self(
            warehouseId: $warehouseId,
            description: $description
        );
    }

    public function isAtWarehouse(): bool
    {
        return $this->warehouseId !== null && $this->rackId === null;
    }

    public function isAtRack(): bool
    {
        return $this->rackId !== null;
    }

    public function isInstalled(): bool
    {
        return $this->description !== null && $this->rackId === null;
    }

    public function toArray(): array
    {
        return [
            'warehouse_id' => $this->warehouseId?->value,
            'rack_id' => $this->rackId?->value,
            'rack_position' => $this->rackPosition,
            'shelf' => $this->shelf,
            'bin' => $this->bin,
            'description' => $this->description,
        ];
    }
}
