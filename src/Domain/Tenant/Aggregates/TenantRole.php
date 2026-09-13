<?php

namespace Src\Domain\Tenant\Aggregates;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TenantRole extends AggregateRoot
{
    private array $permissions = [];
    private bool $isSystemRole = false;

    public function __construct(
        Uuid $id,
        private readonly Uuid $tenantId,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $description = null
    ) {
        parent::__construct($id);
    }

    public static function create(
        Uuid $tenantId,
        string $name,
        string $slug,
        ?string $description = null
    ): self {
        return new self(
            Uuid::generate(),
            $tenantId,
            $name,
            $slug,
            $description
        );
    }

    public static function createAdministrator(Uuid $tenantId): self
    {
        $role = new self(
            Uuid::generate(),
            $tenantId,
            'Administrator',
            'administrator',
            'Full access to all tenant resources'
        );
        $role->isSystemRole = true;
        $role->permissions = ['*'];

        return $role;
    }

    public static function createAdmin(Uuid $tenantId): self
    {
        $role = new self(
            Uuid::generate(),
            $tenantId,
            'Admin',
            'admin',
            'Administrative access'
        );
        $role->isSystemRole = true;
        $role->permissions = [
            'users.*',
            'customers.*',
            'billing.*',
            'inventory.*',
            'reports.*',
            'settings.*',
        ];

        return $role;
    }

    public static function createManager(Uuid $tenantId): self
    {
        $role = new self(
            Uuid::generate(),
            $tenantId,
            'Manager',
            'manager',
            'Operational access'
        );
        $role->isSystemRole = true;
        $role->permissions = [
            'customers.view',
            'customers.create',
            'customers.update',
            'inventory.view',
            'inventory.update',
            'tickets.*',
        ];

        return $role;
    }

    public static function createViewer(Uuid $tenantId): self
    {
        $role = new self(
            Uuid::generate(),
            $tenantId,
            'Viewer',
            'viewer',
            'Read-only access'
        );
        $role->isSystemRole = true;
        $role->permissions = [
            'customers.view',
            'inventory.view',
            'reports.view',
        ];

        return $role;
    }

    public function addPermission(string $permission): void
    {
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }
    }

    public function removePermission(string $permission): void
    {
        $this->permissions = array_filter(
            $this->permissions,
            fn($p) => $p !== $permission && $p !== '*'
        );
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function hasPermission(string $permission): bool
    {
        if (in_array('*', $this->permissions)) {
            return true;
        }

        foreach ($this->permissions as $perm) {
            if ($perm === $permission) {
                return true;
            }

            if (str_ends_with($perm, '.*')) {
                $prefix = rtrim($perm, '.*');
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getTenantId(): Uuid
    {
        return $this->tenantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function isSystemRole(): bool
    {
        return $this->isSystemRole;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'tenant_id' => $this->tenantId->toString(),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'is_system_role' => $this->isSystemRole,
        ];
    }
}
