<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class MaterialUsage {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workOrderId,
        public readonly string $materialId,
        public string $materialName,
        public int $quantity,
        public ?string $serialNumber = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $workOrderId,
        string $materialId,
        string $materialName,
        int $quantity,
        ?string $serialNumber = null,
    ): self {
        return new self(
            Uuid::random(),
            $workOrderId,
            $materialId,
            $materialName,
            $quantity,
            $serialNumber
        );
    }
}
