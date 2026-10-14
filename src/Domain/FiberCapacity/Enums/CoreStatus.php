<?php

namespace Src\Domain\FiberCapacity\Enums;

enum CoreStatus: string
{
    case AVAILABLE = 'available';
    case ALLOCATED = 'allocated';
    case RESERVED = 'reserved';
    case MAINTENANCE = 'maintenance';
    case DAMAGED = 'damaged';
    case DECOMMISSIONED = 'decommissioned';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Tersedia',
            self::ALLOCATED => 'Dialokasikan',
            self::RESERVED => 'Direservasi',
            self::MAINTENANCE => 'Pemeliharaan',
            self::DAMAGED => 'Rusak',
            self::DECOMMISSIONED => 'Dinonaktifkan',
        };
    }

    public function isUsable(): bool
    {
        return match($this) {
            self::AVAILABLE, self::RESERVED => true,
            default => false,
        };
    }
}
