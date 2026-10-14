<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\Aggregates\Plugin;
use Src\Domain\Tenant\Repositories\PluginRepositoryInterface;
use Src\Domain\Tenant\Events\PluginInstalledEvent;
use Src\Domain\Tenant\Events\PluginActivatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PluginService
{
    public function __construct(
        private readonly PluginRepositoryInterface $pluginRepository
    ) {}

    public function registerPlugin(
        string $name,
        string $slug,
        string $type,
        string $description,
        string $author,
        ?string $versionRequired = null,
        ?string $license = null,
        ?array $dependencies = null
    ): Plugin {
        Log::info("PluginService: Registering plugin {$slug}");

        $existing = $this->pluginRepository->findBySlug($slug);
        if ($existing) {
            throw new \InvalidArgumentException("Plugin with slug {$slug} already exists");
        }

        $plugin = Plugin::create(
            $name,
            $slug,
            $type,
            $description,
            $author,
            $versionRequired,
            $license,
            $dependencies
        );

        $this->pluginRepository->save($plugin);

        return $plugin;
    }

    public function installPlugin(string $tenantId, string $pluginSlug, string $version): Plugin
    {
        $plugin = $this->pluginRepository->findBySlug($pluginSlug);

        if (!$plugin) {
            throw new \InvalidArgumentException("Plugin not found: {$pluginSlug}");
        }

        if ($plugin->isInstalled()) {
            throw new \RuntimeException("Plugin is already installed");
        }

        $plugin->installVersion($version);
        $this->pluginRepository->save($plugin);

        Event::dispatch(new PluginInstalledEvent(
            $plugin->getId()->toString(),
            $tenantId,
            $plugin->getSlug(),
            $version
        ));

        return $plugin;
    }

    public function activatePlugin(string $tenantId, string $pluginId): Plugin
    {
        $plugin = $this->pluginRepository->findById(Uuid::fromString($pluginId));

        if (!$plugin) {
            throw new \InvalidArgumentException("Plugin not found: {$pluginId}");
        }

        if (!$plugin->isInstalled()) {
            throw new \RuntimeException("Plugin must be installed before activation");
        }

        $plugin->activate();
        $this->pluginRepository->save($plugin);

        Event::dispatch(new PluginActivatedEvent(
            $plugin->getId()->toString(),
            $tenantId,
            $plugin->getSlug()
        ));

        return $plugin;
    }

    public function deactivatePlugin(string $pluginId): void
    {
        $plugin = $this->pluginRepository->findById(Uuid::fromString($pluginId));

        if (!$plugin) {
            throw new \InvalidArgumentException("Plugin not found: {$pluginId}");
        }

        $plugin->deactivate();
        $this->pluginRepository->save($plugin);
    }

    public function uninstallPlugin(string $pluginId): void
    {
        $plugin = $this->pluginRepository->findById(Uuid::fromString($pluginId));

        if (!$plugin) {
            throw new \InvalidArgumentException("Plugin not found: {$pluginId}");
        }

        $plugin->uninstall();
        $this->pluginRepository->save($plugin);
    }

    public function checkUpdate(string $pluginId, string $currentVersion): ?string
    {
        $plugin = $this->pluginRepository->findById(Uuid::fromString($pluginId));

        if (!$plugin) {
            return null;
        }

        return null;
    }

    public function getActivePlugins(string $tenantId): array
    {
        return $this->pluginRepository->findInstalledByTenant(
            Uuid::fromString($tenantId)
        );
    }

    public function validatePluginDependencies(Plugin $plugin, array $activePlugins): array
    {
        $missing = [];

        foreach ($plugin->dependencies ?? [] as $dependency) {
            $found = false;
            foreach ($activePlugins as $active) {
                if ($active->getSlug() === $dependency) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missing[] = $dependency;
            }
        }

        return $missing;
    }
}
