<?php

declare(strict_types=1);

use App\Services\VoucherTemplate\QRCodeService;
use App\Services\VoucherTemplate\BarcodeService;

/**
 * Helper functions for Voucher Templates.
 *
 * Test cases (via comment / manual test):
 *   1. DEMO123                          -> qr_code('DEMO123', 120)
 *   2. http://login.example.com         -> qr_code('http://login.example.com')
 *   3. VCH-0001 (8char)                 -> barcode('VCH-0001', 'code128', 250, 50)
 *   4. useradmin01:pass12 (12+6 char)   -> qr_code('useradmin01:pass12')
 *   5. VC2026A1                         -> barcode('VC2026A1')
 *
 * Semua output: Data URI SVG base64 (bisa dipakai inline di <img src> atau <embed> di DOMPDF.
 */

if (!function_exists('qr_code')) {
    function qr_code(string $text, int $size = 150, string $errorCorrection = 'M'): string
    {
        static $service = null;
        if ($service === null) {
            if (class_exists(QRCodeService::class)) {
                $service = new QRCodeService();
            } else {
                $safe = htmlspecialchars(mb_substr($text, 0, 24), ENT_QUOTES, 'UTF-8');
                $svg = sprintf(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d"><rect width="100%%" height="100%%" fill="#eee"/><text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="12" fill="#666">%s</text></svg>',
                    $size,
                    $size,
                    $safe
                );
                return 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }
        try {
            return $service->generateDataUri($text, $size, $errorCorrection);
        } catch (\Throwable) {
            $safeText = htmlspecialchars(mb_substr($text, 0, 24), ENT_QUOTES, 'UTF-8');
            $svg = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d"><rect width="100%%" height="100%%" fill="#fff" stroke="#ccc"/>'
                . '<text x="50%%" y="45%%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#900">QR ERROR</text>'
                . '<text x="50%%" y="65%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="8" fill="#333">%s</text>'
                . '</svg>',
                $size,
                $size,
                $size,
                $size,
                $safeText
            );
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }
}

if (!function_exists('barcode')) {
    function barcode(string $text, string $type = 'code128', int $w = 300, int $h = 60): string
    {
        static $service = null;
        $type = strtolower(trim($type));

        if ($service === null && $type === 'code128') {
            if (class_exists(BarcodeService::class)) {
                $service = new BarcodeService();
            }
        }

        try {
            if ($type === 'code128' && $service !== null) {
                return $service->generateCode128DataUri($text, $w, $h);
            }

            $safeText = htmlspecialchars(mb_substr($text, 0, 32), ENT_QUOTES, 'UTF-8');
            $fs = max(8, (int)($h / 4));
            $svg = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
                . '<rect width="100%%" height="100%%" fill="#fff" stroke="#ccc"/>'
                . '<text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="%d" fill="#333">%s</text>'
                . '</svg>',
                $w,
                $h,
                $w,
                $h,
                $fs,
                $safeText
            );
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable) {
            $safeText = htmlspecialchars(mb_substr($text, 0, 32), ENT_QUOTES, 'UTF-8');
            $fs = max(8, (int)($h / 4));
            $svg = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
                . '<rect width="100%%" height="100%%" fill="#fff5f5" stroke="#c00"/>'
                . '<text x="50%%" y="40%%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#900">BARCODE ERROR</text>'
                . '<text x="50%%" y="65%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="%d" fill="#333">%s</text>'
                . '</svg>',
                $w,
                $h,
                $w,
                $h,
                $fs,
                $safeText
            );
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }
}

if (!function_exists('voucher_asset')) {
    function voucher_asset(string $path): string
    {
        $trimmed = ltrim($path, '/\\');

        if ($trimmed === '') {
            try {
                if (function_exists('asset')) {
                    return (string) asset('');
                }
            } catch (\Throwable) {
            }
            return '';
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://') || str_starts_with($trimmed, 'data:')) {
            return $trimmed;
        }

        try {
            if (function_exists('asset')) {
                return (string) asset($trimmed);
            }
        } catch (\Throwable) {
        }

        try {
            if (function_exists('url')) {
                return (string) url($trimmed);
            }
        } catch (\Throwable) {
        }

        try {
            if (class_exists(\Illuminate\Support\Facades\URL::class)) {
                return (string) \Illuminate\Support\Facades\URL::to('/' . $trimmed);
            }
        } catch (\Throwable) {
        }

        return '/' . $trimmed;
    }
}
