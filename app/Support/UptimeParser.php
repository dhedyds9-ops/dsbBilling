<?php

namespace App\Support;

class UptimeParser
{
    public static function toSeconds(string $uptime): int
    {
        $uptime = strtolower(trim($uptime));
        if ($uptime === '' || $uptime === '0') {
            return 0;
        }

        if (preg_match('/^(?:(\d+)w)?(?:(\d+)d)?(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/', $uptime, $m)) {
            $weeks = (int)($m[1] ?? 0);
            $days = (int)($m[2] ?? 0);
            $hours = (int)($m[3] ?? 0);
            $minutes = (int)($m[4] ?? 0);
            $seconds = (int)($m[5] ?? 0);
            if ($weeks + $days + $hours + $minutes + $seconds > 0) {
                return ($weeks * 604800)
                    + ($days * 86400)
                    + ($hours * 3600)
                    + ($minutes * 60)
                    + $seconds;
            }
        }

        $parts = explode(':', $uptime);
        if (count($parts) === 3 && array_walk($parts, fn($v) => is_numeric(trim($v)))) {
            [$h, $m, $s] = array_map('intval', $parts);
            return ($h * 3600) + ($m * 60) + $s;
        }

        if (is_numeric($uptime)) {
            return (int)$uptime;
        }

        return 0;
    }

    public static function toDateTime(string $uptime): ?\Illuminate\Support\Carbon
    {
        $sec = self::toSeconds($uptime);
        if ($sec <= 0) {
            return null;
        }
        return now()->subSeconds($sec);
    }
}
