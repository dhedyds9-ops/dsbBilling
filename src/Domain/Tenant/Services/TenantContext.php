<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\Aggregates\Tenant;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TenantContext
{
    private static ?Uuid $currentTenantId = null;
    private static ?Tenant $currentTenant = null;

    public static function setCurrentTenant(Tenant $tenant): void
    {
        self::$currentTenantId = $tenant->getId();
        self::$currentTenant = $tenant;
    }

    public static function setCurrentTenantId(Uuid $tenantId): void
    {
        self::$currentTenantId = $tenantId;
        self::$currentTenant = null;
    }

    public static function getCurrentTenantId(): ?Uuid
    {
        return self::$currentTenantId;
    }

    public static function getCurrentTenant(): ?Tenant
    {
        return self::$currentTenant;
    }

    public static function clear(): void
    {
        self::$currentTenantId = null;
        self::$currentTenant = null;
    }

    public static function hasTenant(): bool
    {
        return self::$currentTenantId !== null;
    }

    public static function getDatabasePrefix(): ?string
    {
        return self::$currentTenant?->getDatabasePrefix();
    }

    public static function getIsolationLevel(): string
    {
        return self::$currentTenant?->getIsolationLevel()?->value ?? 'shared';
    }
}
