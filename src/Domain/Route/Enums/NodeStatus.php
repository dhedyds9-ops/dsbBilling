<?php

namespace Src\Domain\Route\Enums;

enum NodeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';
    case OVERLOADED = 'overloaded';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::MAINTENANCE => 'Pemeliharaan',
            self::OVERLOADED => 'Beban Berlebih',
        };
    }

    public function isTraversable(): bool
    {
        return match($this) {
            self::ACTIVE => true,
            default => false,
        };
    }
}
