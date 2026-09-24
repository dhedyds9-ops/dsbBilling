<?php

namespace Src\Domain\Outage\Enums;

enum OutageStatus: string
{
    case DETECTED = 'detected';
    case ANALYZING = 'analyzing';
    case CONFIRMED = 'confirmed';
    case RECOVERY_IN_PROGRESS = 'recovery_in_progress';
    case RESOLVED = 'resolved';
    case PARTIALLY_RESOLVED = 'partially_resolved';

    public function label(): string
    {
        return match($this) {
            self::DETECTED => 'Terdeteksi',
            self::ANALYZING => 'Menganalisis',
            self::CONFIRMED => 'Dikonfirmasi',
            self::RECOVERY_IN_PROGRESS => 'Pemulihan Berlangsung',
            self::RESOLVED => 'Terselesaikan',
            self::PARTIALLY_RESOLVED => 'Sebagian Terselesaikan',
        };
    }

    public function isActive(): bool
    {
        return match($this) {
            self::DETECTED, self::ANALYZING, self::CONFIRMED, self::RECOVERY_IN_PROGRESS => true,
            default => false,
        };
    }

    public function isResolved(): bool
    {
        return match($this) {
            self::RESOLVED, self::PARTIALLY_RESOLVED => true,
            default => false,
        };
    }
}
