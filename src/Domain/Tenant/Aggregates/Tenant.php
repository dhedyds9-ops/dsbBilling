<?php

namespace Src\Domain\Tenant\Aggregates;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Tenant\Enums\TenantStatus;
use Src\Domain\Tenant\Enums\TenantTier;
use Src\Domain\Tenant\Enums\DataIsolationLevel;
use Src\Domain\Tenant\ValueObjects\TenantBranding;
use Src\Domain\Tenant\ValueObjects\TenantLimits;

class Tenant extends AggregateRoot
{
    private TenantStatus $status;
    private TenantTier $tier;
    private DataIsolationLevel $isolationLevel;

    public function __construct(
        Uuid $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $domain,
        private readonly string $timezone,
        private readonly string $locale,
        private readonly ?string $databasePrefix = null,
        private readonly ?TenantBranding $branding = null,
        private readonly ?TenantLimits $limits = null
    ) {
        parent::__construct($id);
        $this->status = TenantStatus::PENDING;
        $this->tier = TenantTier::STARTER;
        $this->isolationLevel = DataIsolationLevel::ISOLATED;
    }

    public static function create(
        string $name,
        string $slug,
        string $domain,
        string $timezone = 'UTC',
        string $locale = 'en',
        ?string $databasePrefix = null
    ): self {
        $id = Uuid::generate();
        $tenant = new self(
            $id,
            $name,
            $slug,
            $domain,
            $timezone,
            $locale,
            $databasePrefix ?? "tenant_{$slug}_"
        );

        return $tenant;
    }

    public function activate(): void
    {
        $this->status = TenantStatus::ACTIVE;
    }

    public function suspend(): void
    {
        $this->status = TenantStatus::SUSPENDED;
    }

    public function terminate(): void
    {
        $this->status = TenantStatus::TERMINATED;
    }

    public function upgradeTier(TenantTier $tier): void
    {
        $this->tier = $tier;
    }

    public function updateBranding(TenantBranding $branding): void
    {
        $this->branding = $branding;
    }

    public function updateLimits(TenantLimits $limits): void
    {
        $this->limits = $limits;
    }

    public function setIsolationLevel(DataIsolationLevel $level): void
    {
        $this->isolationLevel = $level;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getDatabasePrefix(): ?string
    {
        return $this->databasePrefix;
    }

    public function getStatus(): TenantStatus
    {
        return $this->status;
    }

    public function getTier(): TenantTier
    {
        return $this->tier;
    }

    public function getIsolationLevel(): DataIsolationLevel
    {
        return $this->isolationLevel;
    }

    public function getBranding(): ?TenantBranding
    {
        return $this->branding;
    }

    public function getLimits(): ?TenantLimits
    {
        return $this->limits;
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'database_prefix' => $this->databasePrefix,
            'status' => $this->status->value,
            'tier' => $this->tier->value,
            'isolation_level' => $this->isolationLevel->value,
            'branding' => $this->branding?->toArray(),
            'limits' => $this->limits?->toArray(),
        ];
    }
}
