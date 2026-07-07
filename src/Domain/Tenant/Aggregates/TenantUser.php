<?php

namespace Src\Domain\Tenant\Aggregates;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class TenantUser extends AggregateRoot
{
    private array $roles = [];
    private array $customPermissions = [];
    private bool $isOwner = false;

    public function __construct(
        Uuid $id,
        private readonly Uuid $tenantId,
        private readonly Uuid $userId,
        private readonly string $email,
        private readonly ?string $displayName = null
    ) {
        parent::__construct($id);
    }

    public static function create(
        Uuid $tenantId,
        Uuid $userId,
        string $email,
        ?string $displayName = null
    ): self {
        return new self(
            Uuid::generate(),
            $tenantId,
            $userId,
            $email,
            $displayName
        );
    }

    public function assignRole(TenantRole $role): void
    {
        $roleId = $role->getId()->toString();

        if (!isset($this->roles[$roleId])) {
            $this->roles[$roleId] = [
                'role_id' => $roleId,
                'role_slug' => $role->getSlug(),
                'assigned_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ];
        }
    }

    public function removeRole(string $roleId): void
    {
        unset($this->roles[$roleId]);
    }

    public function setAsOwner(): void
    {
        $this->isOwner = true;
    }

    public function addCustomPermission(string $permission): void
    {
        if (!in_array($permission, $this->customPermissions)) {
            $this->customPermissions[] = $permission;
        }
    }

    public function removeCustomPermission(string $permission): void
    {
        $this->customPermissions = array_filter(
            $this->customPermissions,
            fn($p) => $p !== $permission
        );
    }

    public function hasPermission(string $permission, array $tenantRoles): bool
    {
        if ($this->isOwner) {
            return true;
        }

        foreach ($this->customPermissions as $perm) {
            if ($perm === $permission || $perm === '*') {
                return true;
            }
        }

        foreach ($this->roles as $roleData) {
            $roleId = $roleData['role_id'];
            if (isset($tenantRoles[$roleId])) {
                $role = $tenantRoles[$roleId];
                if ($role->hasPermission($permission)) {
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

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCustomPermissions(): array
    {
        return $this->customPermissions;
    }

    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'tenant_id' => $this->tenantId->toString(),
            'user_id' => $this->userId->toString(),
            'email' => $this->email,
            'display_name' => $this->displayName,
            'roles' => $this->roles,
            'custom_permissions' => $this->customPermissions,
            'is_owner' => $this->isOwner,
        ];
    }
}
