<?php

namespace Src\Domain\GIS;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\GIS\Enums\CoordinateSystem;

class CoordinateReferenceSystem extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public CoordinateSystem $system,
        public readonly string $srsName,
        public readonly array $parameters,
        public ?string $description = null,
        public ?array $metadata = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        CoordinateSystem $system,
        string $srsName,
        array $parameters = [],
        ?string $description = null,
        ?array $metadata = null,
    ): self {
        return new self(
            Uuid::random(),
            $system,
            $srsName,
            $parameters,
            $description,
            $metadata,
        );
    }
}
