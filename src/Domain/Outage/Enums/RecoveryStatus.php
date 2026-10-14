<?php

namespace Src\Domain\Outage\Enums;

enum RecoveryStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case VERIFICATION = 'verification';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Tertunda',
            self::IN_PROGRESS => 'Sedang Berlangsung',
            self::VERIFICATION => 'Verifikasi',
            self::COMPLETED => 'Selesai',
            self::FAILED => 'Gagal',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function isActive(): bool
    {
        return match($this) {
            self::PENDING, self::IN_PROGRESS, self::VERIFICATION => true,
            default => false,
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }
}
