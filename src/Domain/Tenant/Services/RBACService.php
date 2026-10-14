<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\Aggregates\TenantRole;
use Src\Domain\Tenant\Aggregates\TenantUser;
use Src\Domain\Tenant\Repositories\TenantRoleRepositoryInterface;
use Src\Domain\Tenant\Repositories\TenantUserRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RBACService
{
    public function __construct(
        private readonly TenantRoleRepositoryInterface $roleRepository,
        private readonly TenantUserRepositoryInterface $userRepository
    ) {}

    public function hasPermission(
        Uuid $tenantId,
        Uuid $userId,
        string $permission
    ): bool {
        $tenantUser = $this->userRepository->findByUserId($tenantId, $userId);

        if (!$tenantUser) {
            return false;
        }

        if ($tenantUser->isOwner()) {
            return true;
        }

        if (in_array($permission, $tenantUser->getCustomPermissions())) {
            return true;
        }

        if (in_array('*', $tenantUser->getCustomPermissions())) {
            return true;
        }

        $roles = $this->roleRepository->findByTenantId($tenantId);
        $rolesMap = [];
        foreach ($roles as $role) {
            $rolesMap[$role->getId()->toString()] = $role;
        }

        return $tenantUser->hasPermission($permission, $rolesMap);
    }

    public function createRole(
        Uuid $tenantId,
        string $name,
        string $slug,
        ?string $description = null
    ): TenantRole {
        $existingRole = $this->roleRepository->findBySlug($tenantId, $slug);
        if ($existingRole) {
            throw new \InvalidArgumentException("Role with slug {$slug} already exists");
        }

        $role = TenantRole::create($tenantId, $name, $slug, $description);
        $this->roleRepository->save($role);

        return $role;
    }

    public function assignRoleToUser(
        Uuid $tenantId,
        Uuid $userId,
        string $roleId
    ): void {
        $tenantUser = $this->userRepository->findByUserId($tenantId, $userId);

        if (!$tenantUser) {
            throw new \InvalidArgumentException("Tenant user not found");
        }

        $role = $this->roleRepository->findById(Uuid::fromString($roleId));

        if (!$role || $role->getTenantId()->toString() !== $tenantId->toString()) {
            throw new \InvalidArgumentException("Role not found or does not belong to tenant");
        }

        $tenantUser->assignRole($role);
        $this->userRepository->save($tenantUser);
    }

    public function revokeRoleFromUser(
        Uuid $tenantId,
        Uuid $userId,
        string $roleId
    ): void {
        $tenantUser = $this->userRepository->findByUserId($tenantId, $userId);

        if (!$tenantUser) {
            throw new \InvalidArgumentException("Tenant user not found");
        }

        $tenantUser->removeRole($roleId);
        $this->userRepository->save($tenantUser);
    }

    public function updateRolePermissions(
        Uuid $tenantId,
        string $roleId,
        array $permissions
    ): TenantRole {
        $role = $this->roleRepository->findById(Uuid::fromString($roleId));

        if (!$role || $role->getTenantId()->toString() !== $tenantId->toString()) {
            throw new \InvalidArgumentException("Role not found or does not belong to tenant");
        }

        if ($role->isSystemRole()) {
            throw new \RuntimeException("Cannot modify system role permissions");
        }

        $role->setPermissions($permissions);
        $this->roleRepository->save($role);

        return $role;
    }

    public function getUserPermissions(Uuid $tenantId, Uuid $userId): array
    {
        $tenantUser = $this->userRepository->findByUserId($tenantId, $userId);

        if (!$tenantUser) {
            return [];
        }

        $permissions = $tenantUser->getCustomPermissions();
        $roles = $this->roleRepository->findByTenantId($tenantId);

        foreach ($tenantUser->getRoles() as $roleData) {
            $roleId = $roleData['role_id'];
            foreach ($roles as $role) {
                if ($role->getId()->toString() === $roleId) {
                    $permissions = array_merge($permissions, $role->getPermissions());
                }
            }
        }

        return array_unique($permissions);
    }

    public function getAvailablePermissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'billing.view',
            'billing.create',
            'billing.update',
            'billing.approve',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'reports.view',
            'reports.export',
            'settings.view',
            'settings.update',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.resolve',
            'plugins.view',
            'plugins.install',
            'plugins.uninstall',
        ];
    }
}
