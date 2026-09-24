<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class SerialNumber extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $serialNumber,
        public readonly Uuid $assetId,
        public readonly Uuid $productId,
        public readonly ?Uuid $vendorId = null,
        public readonly ?DateTimeImmutable $manufacturedDate = null,
        public readonly ?DateTimeImmutable $receivedDate = null,
        public readonly ?string $notes = null,
        public readonly bool $isActive = true
    ) {}

    public static function create(
        string $serialNumber,
        Uuid $assetId,
        Uuid $productId,
        ?Uuid $vendorId = null,
        ?DateTimeImmutable $manufacturedDate = null
    ): self {
        return new self(
            id: Uuid::generate(),
            serialNumber: $serialNumber,
            assetId: $assetId,
            productId: $productId,
            vendorId: $vendorId,
            manufacturedDate: $manufacturedDate,
            receivedDate: new DateTimeImmutable(),
            notes: null,
            isActive: true
        );
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function transferTo(Uuid $newAssetId): void
    {
        $this->assetId = $newAssetId;
    }
}
