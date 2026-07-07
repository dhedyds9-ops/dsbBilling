<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class MACAddressRecord extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $macAddress,
        public readonly Uuid $assetId,
        public readonly Uuid $interfaceType,
        public readonly ?string $interfaceName = null,
        public readonly bool $isActive = true,
        public readonly ?DateTimeImmutable $registeredAt = null
    ) {}

    public static function create(
        string $macAddress,
        Uuid $assetId,
        Uuid $interfaceType,
        ?string $interfaceName = null
    ): self {
        return new self(
            id: Uuid::generate(),
            macAddress: $macAddress,
            assetId: $assetId,
            interfaceType: $interfaceType,
            interfaceName: $interfaceName,
            isActive: true,
            registeredAt: new DateTimeImmutable()
        );
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function getNormalizedMAC(): string
    {
        return strtoupper(str_replace([':', '-', '.'], '', $this->macAddress));
    }
}
