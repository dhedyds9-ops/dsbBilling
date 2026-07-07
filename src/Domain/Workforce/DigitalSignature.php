<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class DigitalSignature extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public readonly Uuid $signerId,
        public string $signerName,
        public string $signaturePath,
        public ?DateTimeImmutable $signedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->signedAt = $signedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $taskId,
        Uuid $signerId,
        string $signerName,
        string $signaturePath,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $signerId,
            $signerName,
            $signaturePath,
        );
    }
}
