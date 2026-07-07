<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\AssetType;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetCategory extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
        public readonly ?Uuid $parentId,
        public readonly AssetType $type,
        public readonly bool $requiresSerialNumber = false,
        public readonly bool $requiresMacAddress = false,
        public readonly ?int $depreciationMonths = null,
        public readonly bool $isActive = true
    ) {}

    public static function create(
        string $name,
        string $code,
        AssetType $type,
        ?Uuid $parentId = null,
        ?string $description = null,
        bool $requiresSerialNumber = false,
        bool $requiresMacAddress = false,
        ?int $depreciationMonths = null
    ): self {
        return new self(
            id: Uuid::generate(),
            name: $name,
            code: $code,
            description: $description,
            parentId: $parentId,
            type: $type,
            requiresSerialNumber: $requiresSerialNumber,
            requiresMacAddress: $requiresMacAddress,
            depreciationMonths: $depreciationMonths
        );
    }

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    public function hasChildren(): bool
    {
        return false; // Would be checked via repository
    }

    public function calculateDepreciation(float $price): float
    {
        if ($this->depreciationMonths === null) {
            return $price;
        }

        $monthsElapsed = 0; // Would be calculated from purchase date
        $depreciationPerMonth = $price / $this->depreciationMonths;
        $totalDepreciation = $depreciationPerMonth * $monthsElapsed;

        return max(0, $price - $totalDepreciation);
    }
}
