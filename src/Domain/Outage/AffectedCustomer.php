<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AffectedCustomer extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $outageId,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $customerCode,
        public ImpactLevel $impactLevel,
        public int $serviceAffectedCount = 0,
        public int $totalServiceCount = 0,
        public ?DateTimeImmutable $affectedSince = null,
        public ?DateTimeImmutable $recoveredAt = null,
        public bool $notified = false,
        public bool $compensationApplied = false,
        public array $affectedServices = [],
        public array $contactAttempts = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $outageId,
        string $customerId,
        string $customerName,
        string $customerCode,
        ImpactLevel $impactLevel,
        array $affectedServices = []
    ): self {
        return new self(
            $id,
            $outageId,
            $customerId,
            $customerName,
            $customerCode,
            $impactLevel,
            count($affectedServices),
            count($affectedServices),
            new DateTimeImmutable(),
            null,
            false,
            false,
            $affectedServices
        );
    }

    public function markNotified(string $channel, string $message): void
    {
        $this->notified = true;
        $this->contactAttempts[] = [
            'timestamp' => new DateTimeImmutable(),
            'channel' => $channel,
            'message' => $message,
            'success' => true
        ];
    }

    public function recover(): void
    {
        $this->recoveredAt = new DateTimeImmutable();
        $this->impactLevel = ImpactLevel::NONE;
    }

    public function applyCompensation(float $amount, string $reason): void
    {
        $this->compensationApplied = true;
        $this->metadata['compensation'] = [
            'amount' => $amount,
            'reason' => $reason,
            'applied_at' => new DateTimeImmutable()
        ];
    }

    public function getDowntimeMinutes(): ?int
    {
        if ($this->recoveredAt === null) {
            return null;
        }
        
        $since = $this->affectedSince ?? new DateTimeImmutable();
        return (int) (($this->recoveredAt->getTimestamp() - $since->getTimestamp()) / 60);
    }

    public function getAffectedPercentage(): float
    {
        if ($this->totalServiceCount === 0) {
            return 100.0;
        }
        return ($this->serviceAffectedCount / $this->totalServiceCount) * 100;
    }

    public function isRecovered(): bool
    {
        return $this->recoveredAt !== null;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
