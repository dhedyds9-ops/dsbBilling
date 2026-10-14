<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\WarehouseType;
use Src\Domain\Inventory\ValueObjects\AssetLocation;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class Warehouse extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $code,
        public readonly WarehouseType $type,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $province,
        public readonly ?string $postalCode,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?Uuid $managerId,
        public readonly bool $isActive = true,
        public readonly bool $allowsReturns = true,
        public readonly ?int $capacity = null
    ) {}

    public static function create(
        string $name,
        string $code,
        WarehouseType $type,
        ?string $address = null,
        ?string $city = null,
        ?Uuid $managerId = null
    ): self {
        return new self(
            id: Uuid::generate(),
            name: $name,
            code: $code,
            type: $type,
            address: $address,
            city: $city,
            province: null,
            postalCode: null,
            phone: null,
            email: null,
            managerId: $managerId,
            isActive: true,
            allowsReturns: $type->isInternal(),
            capacity: null
        );
    }

    public function canReceiveStock(): bool
    {
        return $this->type->canReceiveStock() && $this->isActive;
    }

    public function canIssueStock(): bool
    {
        return $this->type->canIssueStock() && $this->isActive;
    }

    public function isInternal(): bool
    {
        return $this->type->isInternal();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postalCode
        ]);
        return implode(', ', $parts);
    }
}
