<?php

namespace Src\Domain\Plugin;

class PluginManager
{
    private array $plugins = [];

    public function register(string $name, PluginInterface $plugin): void
    {
        $this->plugins[$name] = $plugin;
    }

    public function get(string $name): ?PluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->plugins[$name]);
    }

    public function all(): array
    {
        return $this->plugins;
    }

    public function activate(string $name): void
    {
        if ($this->has($name)) {
            $this->plugins[$name]->activate();
        }
    }

    public function deactivate(string $name): void
    {
        if ($this->has($name)) {
            $this->plugins[$name]->deactivate();
        }
    }
}
