<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

/**
 * Pure-PHP CODE 128 Barcode Generator (Code Set B)
 *
 * Test cases (via comment / manual test):
 *   1. DEMO123                          (short alnum)
 *   2. VCH-0001-A7                      (voucher code 10 char)
 *   3. useradmin01                      (username 12 char)
 *   4. http://login.example.com/q?v=1   (URL panjang printable)
 *   5. INV-2026-00001234                (invoice no)
 *
 * Spec: GS1 General Specification / ISO/IEC 15417 CODE 128
 *   Start B = 104
 *   Stop    = 106 + 2-module bar (akhir)
 *   Checksum: (Start + Σ(position * value)) mod 103
 *
 * Output: Data URI SVG (base64) - renderable di browser & DOMPDF inline.
 */
class BarcodeService
{
    private const CODE_PATTERNS = [
        '11011001100', '11001101100', '11001100110', '10010011000', '10010001100',
        '10001001100', '10011001000', '10011000100', '10001100100', '11001001000',
        '11001000100', '11000100100', '10110011100', '10011011100', '10011001110',
        '10111001100', '10011101100', '10011100110', '11001110010', '11001011100',
        '11001001110', '11011100100', '11001110100', '11101101110', '11101001100',
        '11100101100', '11100100110', '11101100100', '11100110100', '11100110010',
        '11011011000', '11011000110', '11000110110', '10100011000', '10001011000',
        '10001000110', '10110001000', '10001101000', '10001100010', '11010001000',
        '11000101000', '11000100010', '10110111000', '10110001110', '10001101110',
        '10111011000', '10111000110', '10001110110', '11101110110', '11010001110',
        '11000101110', '11011101000', '11011100010', '11011101110', '11101011000',
        '11101000110', '11100010110', '11101101000', '11101100010', '11100011010',
        '11101111010', '11001000010', '11110001010', '10100110000', '10100001100',
        '10010110000', '10010000110', '10000101100', '10000100110', '10110010000',
        '10110000100', '10011010000', '10011000010', '10000110100', '10000110010',
        '11000010010', '11001010000', '11110111010', '11000010100', '10001111010',
        '10100111100', '10010111100', '10010011110', '10111100100', '10011110100',
        '10011110010', '11110100100', '11110010100', '11110010010', '11011011110',
        '11011110110', '11110110110', '10101111000', '10100011110', '10001011110',
        '10111101000', '10111100010', '11110101000', '11110100010', '10111011110',
        '10111101110', '11101011110', '11110101110', '11010000100', '11010010000',
        '11010011100', '1100011101011',
    ];

    private const START_A = 103;
    private const START_B = 104;
    private const START_C = 105;
    private const STOP_CODE = 106;

    public function generateCode128DataUri(string $text, int $width = 300, int $height = 60): string
    {
        $pattern = $this->encodeCode128B($text);
        $totalModules = strlen($pattern);
        if ($totalModules === 0) {
            return $this->placeholderDataUri($text, $width, $height);
        }
        $quietLeft = 10;
        $quietRight = 10;
        $totalModules += $quietLeft + $quietRight;

        $moduleWidth = $width / $totalModules;
        if ($moduleWidth < 1) {
            $moduleWidth = 1;
        }

        $bars = '';
        $x = $quietLeft * $moduleWidth;
        $lastWasDark = false;
        $runStart = 0;
        for ($i = 0; $i < strlen($pattern); $i++) {
            $isDark = ($pattern[$i] === '1');
            if ($i === 0) {
                $lastWasDark = $isDark;
                $runStart = $x;
            } elseif ($isDark !== $lastWasDark) {
                if ($lastWasDark) {
                    $runWidth = $x - $runStart;
                    if ($runWidth > 0) {
                        $bars .= sprintf(
                            '<rect x="%.4f" y="0" width="%.4f" height="%d" fill="#000"/>',
                            $runStart,
                            $runWidth,
                            $height
                        );
                    }
                }
                $lastWasDark = $isDark;
                $runStart = $x;
            }
            $x += $moduleWidth;
        }
        if ($lastWasDark) {
            $runWidth = $x - $runStart;
            if ($runWidth > 0) {
                $bars .= sprintf(
                    '<rect x="%.4f" y="0" width="%.4f" height="%d" fill="#000"/>',
                    $runStart,
                    $runWidth,
                    $height
                );
            }
        }

        $labelHeight = 0;
        $label = '';
        $labelFontSize = max(8, (int)($height * 0.22));
        if ($height > 40) {
            $labelHeight = (int)($labelFontSize * 1.4);
            $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            $label = sprintf(
                '<text x="50%%" y="%d" dominant-baseline="auto" text-anchor="middle" font-family="monospace" font-size="%d" fill="#000">%s</text>',
                $height + $labelFontSize,
                $labelFontSize,
                $safeText
            );
        }

        $totalHeight = $height + $labelHeight;
        $actualWidth = max($width, (int)ceil($totalModules * $moduleWidth));

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" shape-rendering="crispEdges">'
            . '<rect width="100%%" height="100%%" fill="#fff"/>'
            . '%s%s'
            . '</svg>',
            $actualWidth,
            $totalHeight,
            $actualWidth,
            $totalHeight,
            $bars,
            $label
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function placeholderDataUri(string $text, int $width, int $height): string
    {
        $safeText = htmlspecialchars(substr($text, 0, 24), ENT_QUOTES, 'UTF-8');
        $fs = max(8, (int)($height / 4));
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
            . '<rect width="100%%" height="100%%" fill="#f5f5f5" stroke="#999" stroke-width="1"/>'
            . '<text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="%d" fill="#333">BC: %s</text>'
            . '</svg>',
            $width,
            $height,
            $width,
            $height,
            $fs,
            $safeText
        );
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function encodeCode128B(string $text): string
    {
        $values = [];
        for ($i = 0; $i < strlen($text); $i++) {
            $ord = ord($text[$i]);
            if ($ord < 32 || $ord > 127) {
                throw new \InvalidArgumentException('CODE 128 B hanya mendukung ASCII printable (0x20-0x7E) dan SPACE');
            }
            if ($ord === 127) {
                $values[] = 95;
            } else {
                $values[] = $ord - 32;
            }
        }

        $checksum = self::START_B;
        foreach ($values as $pos => $val) {
            $checksum += ($pos + 1) * $val;
        }
        $checksum = $checksum % 103;

        $symbols = array_merge([self::START_B], $values, [$checksum, self::STOP_CODE]);

        $pattern = '';
        foreach ($symbols as $s) {
            if (isset(self::CODE_PATTERNS[$s])) {
                $pattern .= self::CODE_PATTERNS[$s];
            } else {
                throw new \RuntimeException("Simbol CODE 128 tidak valid: {$s}");
            }
        }
        return $pattern;
    }
}
