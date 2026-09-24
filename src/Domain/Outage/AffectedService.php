<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AffectedService extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $outageId,
        public readonly string $customerId,
        public readonly string $serviceId,
        public readonly string $serviceName,
        public readonly string $serviceType,
        public ImpactLevel $impactLevel,
        public ?DateTimeImmutable $affectedSince = null,
        public ?DateTimeImmutable $recoveredAt = null,
        public ?string $replacementServiceId = null,
        public bool $backupActivated = false,
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $outageId,
        string $customerId,
        string $serviceId,
        string $serviceName,
        string $serviceType,
        ImpactLevel $impactLevel
    ): self {
        return new self(
            $id,
            $outageId,
            $customerId,
            $serviceId,
            $serviceName,
            $serviceType,
            $impactLevel,
            new DateTimeImmutable()
        );
    }

    public function recover(): void
    {
        $this->recoveredAt = new DateTimeImmutable();
        $this->impactLevel = ImpactLevel::NONE;
    }

    public function activateBackup(string $replacementServiceId): void
    {
        $this->backupActivated = true;
        $this->replacementServiceId = $replacementServiceId;
        $this->metadata['backup_activated_at'] = new DateTimeImmutable();
    }

    public function getDowntimeMinutes(): ?int
    {
        if ($this->recoveredAt === null) {
            return null;
        }
        
        $since = $this->affectedSince ?? new DateTimeImmutable();
        return (int) (($this->recoveredAt->getTimestamp() - $since->getTimestamp()) / 60);
    }

    public function isRecovered(): bool
    {
        return $this->recoveredAt !== null;
    }

    public function isBackedUp(): bool
    {
        return $this->backupActivated;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
