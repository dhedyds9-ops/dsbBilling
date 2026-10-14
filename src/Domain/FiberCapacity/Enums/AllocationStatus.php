<?php

namespace Src\Domain\FiberCapacity\Enums;

enum AllocationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case ACTIVE = 'active';
    case RELEASED = 'released';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Tertunda',
            self::APPROVED => 'Disetujui',
            self::ACTIVE => 'Aktif',
            self::RELEASED => 'Dirilis',
            self::REJECTED => 'Ditolak',
            self::EXPIRED => 'Kedaluwarsa',
        };
    }

    public function isActive(): bool
    {
        return match($this) {
            self::ACTIVE, self::APPROVED => true,
            default => false,
        };
    }
}
