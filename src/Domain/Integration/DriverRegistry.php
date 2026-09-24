<?php

namespace Src\Domain\Integration;

use InvalidArgumentException;
use Src\Domain\Integration\Contracts\RouterDriverInterface;
use Src\Domain\Integration\Contracts\RadiusDriverInterface;
use Src\Domain\Integration\Contracts\OLTDriverInterface;
use Src\Domain\Integration\Contracts\PaymentDriverInterface;
use Src\Domain\Integration\Contracts\NotificationDriverInterface;

class DriverRegistry
{
    private array $drivers = [];

    public function register(string $type, string $name, callable $factory): void
    {
        $this->drivers[$type][$name] = $factory;
    }

    public function get(string $type, string $name, array $config): mixed
    {
        if (!isset($this->drivers[$type][$name])) {
            throw new InvalidArgumentException("Driver {$name} for type {$type} not found");
        }

        return call_user_func($this->drivers[$type][$name], $config);
    }

    public function has(string $type, string $name): bool
    {
        return isset($this->drivers[$type][$name]);
    }

    public function list(string $type): array
    {
        return array_keys($this->drivers[$type] ?? []);
    }

    public const TYPE_ROUTER = 'router';
    public const TYPE_RADIUS = 'radius';
    public const TYPE_OLT = 'olt';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_NOTIFICATION = 'notification';
}
