<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\Aggregates\Tenant;
use Src\Domain\Tenant\Aggregates\TenantRole;
use Src\Domain\Tenant\Aggregates\TenantUser;
use Src\Domain\Tenant\Aggregates\License;
use Src\Domain\Tenant\Enums\TenantStatus;
use Src\Domain\Tenant\Enums\TenantTier;
use Src\Domain\Tenant\ValueObjects\TenantBranding;
use Src\Domain\Tenant\ValueObjects\TenantLimits;
use Src\Domain\Tenant\Repositories\TenantRepositoryInterface;
use Src\Domain\Tenant\Repositories\TenantRoleRepositoryInterface;
use Src\Domain\Tenant\Repositories\TenantUserRepositoryInterface;
use Src\Domain\Tenant\Repositories\LicenseRepositoryInterface;
use Src\Domain\Tenant\Events\TenantCreatedEvent;
use Src\Domain\Tenant\Events\TenantActivatedEvent;
use Src\Domain\Tenant\Events\TenantSuspendedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class TenantManagerService
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantRoleRepositoryInterface $roleRepository,
        private readonly TenantUserRepositoryInterface $userRepository,
        private readonly LicenseRepositoryInterface $licenseRepository
    ) {}

    public function createTenant(
        string $name,
        string $slug,
        string $domain,
        string $timezone = 'UTC',
        string $locale = 'en',
        ?TenantBranding $branding = null,
        ?TenantLimits $limits = null
    ): Tenant {
        Log::info("TenantManager: Creating tenant {$slug}");

        $tenant = Tenant::create($name, $slug, $domain, $timezone, $locale);
        $tenant->updateBranding($branding ?? new TenantBranding());
        $tenant->updateLimits($limits ?? new TenantLimits());

        $this->tenantRepository->save($tenant);

        $this->createDefaultRoles($tenant);

        Event::dispatch(new TenantCreatedEvent(
            $tenant->getId()->toString(),
            $tenant->getName(),
            $tenant->getSlug(),
            $tenant->getDomain()
        ));

        Log::info("TenantManager: Tenant {$slug} created successfully");

        return $tenant;
    }

    public function activateTenant(string $tenantId): Tenant
    {
        $tenant = $this->tenantRepository->findById(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($tenantId)
        );

        if (!$tenant) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        $license = $this->licenseRepository->findByTenantId($tenant->getId());

        if (!$license || !$license->isActive()) {
            throw new \RuntimeException("Cannot activate tenant without active license");
        }

        $tenant->activate();
        $this->tenantRepository->save($tenant);

        Event::dispatch(new TenantActivatedEvent(
            $tenant->getId()->toString(),
            $tenant->getName()
        ));

        return $tenant;
    }

    public function suspendTenant(string $tenantId, string $reason): void
    {
        $tenant = $this->tenantRepository->findById(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($tenantId)
        );

        if (!$tenant) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        $tenant->suspend();
        $this->tenantRepository->save($tenant);

        Event::dispatch(new TenantSuspendedEvent(
            $tenant->getId()->toString(),
            $reason
        ));
    }

    public function updateBranding(string $tenantId, TenantBranding $branding): Tenant
    {
        $tenant = $this->tenantRepository->findById(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($tenantId)
        );

        if (!$tenant) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        $limits = $tenant->getLimits();
        if ($limits && !$limits->allowCustomBranding) {
            throw new \RuntimeException("Custom branding not allowed for this tier");
        }

        $tenant->updateBranding($branding);
        $this->tenantRepository->save($tenant);

        return $tenant;
    }

    public function upgradeTier(string $tenantId, TenantTier $newTier): Tenant
    {
        $tenant = $this->tenantRepository->findById(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($tenantId)
        );

        if (!$tenant) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        $tenant->upgradeTier($newTier);
        $this->tenantRepository->save($tenant);

        return $tenant;
    }

    public function checkLimits(string $tenantId): array
    {
        $tenant = $this->tenantRepository->findById(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($tenantId)
        );

        if (!$tenant) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        $limits = $tenant->getLimits();
        $violations = [];

        if ($limits) {
            $currentUsers = count($this->userRepository->findByTenantId($tenant->getId()));
            if ($currentUsers > $limits->maxUsers) {
                $violations[] = [
                    'type' => 'max_users',
                    'current' => $currentUsers,
                    'limit' => $limits->maxUsers,
                ];
            }
        }

        return $violations;
    }

    private function createDefaultRoles(Tenant $tenant): void
    {
        $roles = [
            TenantRole::createAdministrator($tenant->getId()),
            TenantRole::createAdmin($tenant->getId()),
            TenantRole::createManager($tenant->getId()),
            TenantRole::createViewer($tenant->getId()),
        ];

        foreach ($roles as $role) {
            $this->roleRepository->save($role);
        }
    }
}
