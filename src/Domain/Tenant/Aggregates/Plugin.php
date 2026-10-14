<?php

namespace Src\Domain\Tenant\Aggregates;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Tenant\Enums\PluginStatus;

class Plugin extends AggregateRoot
{
    private PluginStatus $status;
    private ?string $version = null;
    private ?string $installedAt = null;
    private ?string $activatedAt = null;

    public function __construct(
        Uuid $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $type,
        private readonly string $description,
        private readonly string $author,
        private readonly ?string $versionRequired = null,
        private readonly ?string $license = null,
        private readonly ?array $dependencies = null,
        private readonly ?array $permissions = null
    ) {
        parent::__construct($id);
        $this->status = PluginStatus::INSTALLED;
    }

    public static function create(
        string $name,
        string $slug,
        string $type,
        string $description,
        string $author,
        ?string $versionRequired = null,
        ?string $license = null,
        ?array $dependencies = null
    ): self {
        return new self(
            Uuid::generate(),
            $name,
            $slug,
            $type,
            $description,
            $author,
            $versionRequired,
            $license,
            $dependencies
        );
    }

    public function activate(): void
    {
        if ($this->status === PluginStatus::INSTALLED || $this->status === PluginStatus::INACTIVE) {
            $this->status = PluginStatus::ACTIVE;
            $this->activatedAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        }
    }

    public function deactivate(): void
    {
        if ($this->status === PluginStatus::ACTIVE) {
            $this->status = PluginStatus::INACTIVE;
        }
    }

    public function uninstall(): void
    {
        $this->status = PluginStatus::UNINSTALLED;
    }

    public function markUpdateAvailable(string $newVersion): void
    {
        $this->status = PluginStatus::UPDATE_AVAILABLE;
        $this->version = $newVersion;
    }

    public function installVersion(string $version): void
    {
        $this->version = $version;
        $this->installedAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function requiresPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function isActive(): bool
    {
        return $this->status === PluginStatus::ACTIVE;
    }

    public function isInstalled(): bool
    {
        return $this->status === PluginStatus::INSTALLED
            || $this->status === PluginStatus::ACTIVE
            || $this->status === PluginStatus::UPDATE_AVAILABLE;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getStatus(): PluginStatus
    {
        return $this->status;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getInstalledAt(): ?string
    {
        return $this->installedAt;
    }

    public function getActivatedAt(): ?string
    {
        return $this->activatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'author' => $this->author,
            'version' => $this->version,
            'version_required' => $this->versionRequired,
            'license' => $this->license,
            'status' => $this->status->value,
            'installed_at' => $this->installedAt,
            'activated_at' => $this->activatedAt,
            'dependencies' => $this->dependencies,
            'permissions' => $this->permissions,
        ];
    }
}
