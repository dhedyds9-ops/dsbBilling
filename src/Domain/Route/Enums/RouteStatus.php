<?php

namespace Src\Domain\Route\Enums;

enum RouteStatus: string
{
    case PENDING = 'pending';
    case CALCULATING = 'calculating';
    case CALCULATED = 'calculated';
    case OPTIMIZED = 'optimized';
    case ACTIVE = 'active';
    case DEACTIVATED = 'deactivated';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Tertunda',
            self::CALCULATING => 'Menghitung',
            self::CALCULATED => 'Dihitung',
            self::OPTIMIZED => 'Dioptimasi',
            self::ACTIVE => 'Aktif',
            self::DEACTIVATED => 'Dinonaktifkan',
            self::FAILED => 'Gagal',
        };
    }

    public function isUsable(): bool
    {
        return match($this) {
            self::CALCULATED, self::OPTIMIZED, self::ACTIVE => true,
            default => false,
        };
    }
}
