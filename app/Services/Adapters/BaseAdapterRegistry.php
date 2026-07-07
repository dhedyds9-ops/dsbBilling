<?php

namespace App\Services\Adapters;

class BaseAdapterRegistry
{
    protected array $adapters = [];

    public function register(string $name, object $adapter): void
    {
        $this->adapters[$name] = $adapter;
    }

    public function get(string $name): ?object
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
