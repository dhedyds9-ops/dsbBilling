<?php

namespace Src\Domain\Tenant\Aggregates;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Tenant\Enums\LicenseType;
use Src\Domain\Tenant\Enums\LicenseStatus;
use Src\Domain\Tenant\ValueObjects\LicenseKey;

class License extends AggregateRoot
{
    private LicenseStatus $status;

    public function __construct(
        Uuid $id,
        private readonly Uuid $tenantId,
        private readonly LicenseType $type,
        private readonly \DateTimeImmutable $issuedAt,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly ?LicenseKey $licenseKey = null,
        private readonly ?int $maxActivations = null,
        private readonly int $activationCount = 0
    ) {
        parent::__construct($id);
        $this->status = LicenseStatus::ACTIVE;
    }

    public static function createSubscription(
        Uuid $tenantId,
        \DateTimeImmutable $expiresAt,
        ?LicenseKey $licenseKey = null
    ): self {
        return new self(
            Uuid::generate(),
            $tenantId,
            LicenseType::SUBSCRIPTION,
            new \DateTimeImmutable(),
            $expiresAt,
            $licenseKey
        );
    }

    public static function createPerpetual(
        Uuid $tenantId,
        ?LicenseKey $licenseKey = null,
        ?int $maxActivations = null
    ): self {
        return new self(
            Uuid::generate(),
            $tenantId,
            LicenseType::PERPETUAL,
            new \DateTimeImmutable(),
            new \DateTimeImmutable('9999-12-31'),
            $licenseKey,
            $maxActivations
        );
    }

    public static function createTrial(
        Uuid $tenantId,
        int $trialDays = 14
    ): self {
        return new self(
            Uuid::generate(),
            $tenantId,
            LicenseType::TRIAL,
            new \DateTimeImmutable(),
            new \DateTimeImmutable("+{$trialDays} days")
        );
    }

    public function activate(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->maxActivations !== null && $this->activationCount >= $this->maxActivations) {
            return false;
        }

        $this->activationCount++;
        return true;
    }

    public function suspend(): void
    {
        $this->status = LicenseStatus::SUSPENDED;
    }

    public function cancel(): void
    {
        $this->status = LicenseStatus::CANCELLED;
    }

    public function renew(\DateTimeImmutable $newExpiresAt): void
    {
        $this->expiresAt = $newExpiresAt;
        $this->status = LicenseStatus::ACTIVE;
    }

    public function isActive(): bool
    {
        if ($this->status !== LicenseStatus::ACTIVE) {
            return false;
        }

        return $this->expiresAt > new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable();
    }

    public function getTenantId(): Uuid
    {
        return $this->tenantId;
    }

    public function getType(): LicenseType
    {
        return $this->type;
    }

    public function getStatus(): LicenseStatus
    {
        return $this->status;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getLicenseKey(): ?LicenseKey
    {
        return $this->licenseKey;
    }

    public function getMaxActivations(): ?int
    {
        return $this->maxActivations;
    }

    public function getActivationCount(): int
    {
        return $this->activationCount;
    }

    public function getRemainingDays(): int
    {
        $now = new \DateTimeImmutable();
        $diff = $this->expiresAt->diff($now);

        return max(0, $this->expiresAt > $now ? $diff->days : 0);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'tenant_id' => $this->tenantId->toString(),
            'type' => $this->type->value,
            'status' => $this->status->value,
            'issued_at' => $this->issuedAt->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'license_key' => $this->licenseKey?->toArray(),
            'max_activations' => $this->maxActivations,
            'activation_count' => $this->activationCount,
            'is_active' => $this->isActive(),
            'remaining_days' => $this->getRemainingDays(),
        ];
    }
}
