<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PhotoDocumentation extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public string $photoPath,
        public string $description,
        public ?string $latitude = null,
        public ?string $longitude = null,
        public ?DateTimeImmutable $takenAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->takenAt = $takenAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $taskId,
        string $photoPath,
        string $description,
        ?string $latitude = null,
        ?string $longitude = null,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $photoPath,
            $description,
            $latitude,
            $longitude,
        );
    }

    public function updateDescription(string $description): void {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePhotoPath(string $photoPath): void {
        $this->photoPath = $photoPath;
        $this->updatedAt = new DateTimeImmutable();
    }
}
