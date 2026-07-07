<?php

namespace Src\Domain\FiberCapacity\Enums;

enum PortStatus: string
{
    case AVAILABLE = 'available';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case FAULTY = 'faulty';
    case SHUTDOWN = 'shutdown';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Tersedia',
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::FAULTY => 'Bermasalah',
            self::SHUTDOWN => 'Dimatikan',
        };
    }

    public function canProvision(): bool
    {
        return match($this) {
            self::AVAILABLE, self::ACTIVE => true,
            default => false,
        };
    }
}
