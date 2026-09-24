<?php

namespace App\Services\Adapters;

class BaseAdapterRegistry
{
    protected array $adapters = [];

    public function register(string $name, string|object $adapter): void
    {
        $this->adapters[$name] = $adapter;
    }

    public function get(string $name): string|object|null
    {
        return $this->adapters[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->adapters[$name]);
    }

    public function all(): array
    {
        return $this->adapters;
    }
}
