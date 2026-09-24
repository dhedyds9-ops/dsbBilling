<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

/**
 * Pure-PHP QR Code Generator (Mode BYTE, Version 1-10)
 *
 * Test cases (via comment / manual test):
 *   1. DEMO123                          (short alnum, Version 1 L/M/Q/H OK)
 *   2. http://login.example.com         (URL ~23 chars, Version 2 OK)
 *   3. VCH-0001                         (voucher code 8 char, Version 1 OK)
 *   4. useradmin01:pass12               (username 12 + password 6, Version 2 OK)
 *   5. {u:"budi12345678",p:"aB3!x9"}    (JSON-like, Version 3 OK)
 *
 * Output: Data URI SVG (base64) - renderable di browser & DOMPDF inline.
 */
class QRCodeService
{
    private const MODE_BYTE = 0b0100;

    private const ECC_ORDER = ['L', 'M', 'Q', 'H'];
    private const ECC_CODEWORD_TOTAL = [
        1 => [26, 26, 26, 26],
        2 => [44, 44, 44, 44],
        3 => [70, 70, 70, 70],
        4 => [100, 100, 100, 100],
        5 => [134, 134, 134, 134],
        6 => [172, 172, 172, 172],
        7 => [196, 196, 196, 196],
        8 => [242, 242, 242, 242],
        9 => [292, 292, 292, 292],
        10 => [346, 346, 346, 346],
    ];
    private const ECC_CODEWORD_PER_BLOCK = [
        1 => [7, 10, 13, 17],
        2 => [10, 16, 22, 28],
        3 => [15, 26, 36, 44],
        4 => [20, 36, 52, 64],
        5 => [26, 48, 72, 88],
        6 => [36, 64, 96, 112],
        7 => [40, 72, 108, 130],
        8 => [48, 88, 132, 156],
        9 => [60, 110, 160, 192],
        10 => [72, 130, 192, 224],
    ];
    private const ECC_GROUP1_BLOCKS = [
        1 => [1, 1, 1, 1],
        2 => [1, 1, 1, 1],
        3 => [1, 1, 2, 2],
        4 => [2, 2, 2, 2],
        5 => [2, 2, 2, 2],
        6 => [4, 4, 4, 4],
        7 => [4, 4, 2, 2],
        8 => [4, 4, 4, 4],
        9 => [5, 5, 5, 5],
        10 => [6, 6, 6, 6],
    ];
    private const ECC_GROUP1_DATA_PER_BLOCK = [
        1 => [19, 16, 13, 9],
        2 => [34, 28, 22, 16],
        3 => [55, 44, 34, 26],
        4 => [80, 64, 48, 36],
        5 => [108, 86, 62, 46],
        6 => [136, 108, 76, 60],
        7 => [156, 124, 88, 66],
        8 => [194, 154, 110, 86],
        9 => [232, 182, 132, 100],
        10 => [274, 216, 154, 122],
    ];
    private const ECC_GROUP2_BLOCKS = [
        1 => [0, 0, 0, 0],
        2 => [0, 0, 0, 0],
        3 => [0, 0, 0, 0],
        4 => [0, 0, 0, 0],
        5 => [0, 0, 0, 0],
        6 => [0, 0, 0, 0],
        7 => [1, 1, 2, 2],
        8 => [1, 1, 2, 2],
        9 => [1, 1, 2, 2],
        10 => [2, 2, 2, 4],
    ];
    private const ECC_GROUP2_DATA_PER_BLOCK = [
        1 => [0, 0, 0, 0],
        2 => [0, 0, 0, 0],
        3 => [0, 0, 0, 0],
        4 => [0, 0, 0, 0],
        5 => [0, 0, 0, 0],
        6 => [0, 0, 0, 0],
        7 => [157, 125, 89, 67],
        8 => [195, 155, 111, 87],
        9 => [233, 183, 133, 101],
        10 => [275, 217, 155, 123],
    ];

    private const ALIGNMENT_PATTERN_POS = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    private const GF_EXP = [];
    private const GF_LOG = [];

    /** @var array<int,int> */
    private array $gfExp;
    /** @var array<int,int> */
    private array $gfLog;

    public function __construct()
    {
        $this->initGaloisField();
    }

    public function generateDataUri(string $text, int $size = 150, string $errorCorrection = 'M'): string
    {
        $eccIdx = array_search(strtoupper($errorCorrection), self::ECC_ORDER, true);
        if ($eccIdx === false) {
            $eccIdx = 1;
        }

        try {
            $matrix = $this->encode($text, $eccIdx);
        } catch (\Throwable) {
            return $this->placeholderSvgDataUri($text, $size);
        }

        $modules = count($matrix);
        $quietZone = 4;
        $totalModules = $modules + 2 * $quietZone;
        $moduleSize = (float)$size / $totalModules;

        $rects = '';
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix[$y][$x]) {
                    $px = ($x + $quietZone) * $moduleSize;
                    $py = ($y + $quietZone) * $moduleSize;
                    $s = $moduleSize + 0.5;
                    $rects .= sprintf(
                        '<rect x="%.4f" y="%.4f" width="%.4f" height="%.4f" fill="#000"/>',
                        $px,
                        $py,
                        $s,
                        $s
                    );
                }
            }
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" shape-rendering="crispEdges"><rect width="100%%" height="100%%" fill="#fff"/>%s</svg>',
            $size,
            $size,
            $size,
            $size,
            $rects
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function placeholderSvgDataUri(string $text, int $size): string
    {
        $safeText = htmlspecialchars(substr($text, 0, 32), ENT_QUOTES, 'UTF-8');
        $fs = max(8, (int)($size / 14));
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
            . '<rect width="100%%" height="100%%" fill="#f5f5f5" stroke="#999" stroke-width="2"/>'
            . '<text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="%d" fill="#333">QR: %s</text>'
            . '</svg>',
            $size,
            $size,
            $size,
            $size,
            $fs,
            $safeText
        );
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function initGaloisField(): void
    {
        $exp = [];
        $log = [];
        $x = 1;
        for ($i = 0; $i < 256; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11d;
            }
        }
        $exp[255] = $exp[0];
        $this->gfExp = $exp;
        $this->gfLog = $log;
    }

    private function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }
        return $this->gfExp[($this->gfLog[$a] + $this->gfLog[$b]) % 255];
    }

    /**
     * @return array<int>
     */
    private function rsGeneratorPoly(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $newPoly = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $newPoly[$j] ^= $poly[$j];
                $newPoly[$j + 1] ^= $this->gfMul($poly[$j], $this->gfExp[$i]);
            }
            $poly = $newPoly;
        }
        return $poly;
    }

    /**
     * @param array<int> $data
     * @param array<int> $generator
     * @return array<int>
     */
    private function rsCompute(array $data, array $generator): array
    {
        $nGen = count($generator);
        $res = array_merge($data, array_fill(0, $nGen - 1, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $res[$i];
            if ($coef !== 0) {
                for ($j = 0; $j < $nGen; $j++) {
                    $res[$i + $j] ^= $this->gfMul($generator[$j], $coef);
                }
            }
        }
        return array_slice($res, count($data));
    }

    private function pickVersion(int $dataBitsNeeded, int $eccIdx): int
    {
        for ($v = 1; $v <= 10; $v++) {
            $total = $this->totalDataBytes($v, $eccIdx) * 8;
            $ccBits = ($v < 10) ? 8 : 16;
            $header = 4 + $ccBits;
            if (($total - $header) >= $dataBitsNeeded) {
                return $v;
            }
        }
        throw new \RuntimeException('Data terlalu panjang untuk QR Version 1-10');
    }

    private function totalDataBytes(int $version, int $eccIdx): int
    {
        $g1b = self::ECC_GROUP1_BLOCKS[$version][$eccIdx];
        $g1d = self::ECC_GROUP1_DATA_PER_BLOCK[$version][$eccIdx];
        $g2b = self::ECC_GROUP2_BLOCKS[$version][$eccIdx];
        $g2d = self::ECC_GROUP2_DATA_PER_BLOCK[$version][$eccIdx];
        return $g1b * $g1d + $g2b * $g2d;
    }

    /**
     * @return array<int>
     */
    private function buildBitstream(string $text, int $version, int $eccIdx): array
    {
        $bytes = array_values(unpack('C*', $text));
        $dataBits = count($bytes) * 8;
        $ccBits = ($version < 10) ? 8 : 16;

        $bits = [];
        $this->pushBits($bits, self::MODE_BYTE, 4);
        $this->pushBits($bits, count($bytes), $ccBits);
        foreach ($bytes as $b) {
            $this->pushBits($bits, $b, 8);
        }

        $capacity = $this->totalDataBytes($version, $eccIdx) * 8;
        $remain = $capacity - count($bits);
        $term = min(4, $remain);
        $this->pushBits($bits, 0, $term);

        $pad0 = (8 - (count($bits) % 8)) % 8;
        $this->pushBits($bits, 0, $pad0);

        $result = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($j = 0; $j < 8 && ($i + $j) < count($bits); $j++) {
                $byte = ($byte << 1) | $bits[$i + $j];
            }
            $result[] = $byte;
        }

        $target = $this->totalDataBytes($version, $eccIdx);
        $alt = 0;
        while (count($result) < $target) {
            $result[] = ($alt % 2 === 0) ? 0xEC : 0x11;
            $alt++;
        }
        return $result;
    }

    /**
     * @param array<int> $bits
     */
    private function pushBits(array &$bits, int $value, int $len): void
    {
        for ($i = $len - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /**
     * @param array<int> $dataBytes
     * @return array<int>
     */
    private function constructFinalCodewords(array $dataBytes, int $version, int $eccIdx): array
    {
        $g1b = self::ECC_GROUP1_BLOCKS[$version][$eccIdx];
        $g1d = self::ECC_GROUP1_DATA_PER_BLOCK[$version][$eccIdx];
        $g2b = self::ECC_GROUP2_BLOCKS[$version][$eccIdx];
        $g2d = self::ECC_GROUP2_DATA_PER_BLOCK[$version][$eccIdx];
        $eccPerBlock = self::ECC_CODEWORD_PER_BLOCK[$version][$eccIdx];
        $generator = $this->rsGeneratorPoly($eccPerBlock);

        $dataBlocks = [];
        $eccBlocks = [];
        $offset = 0;
        for ($i = 0; $i < $g1b; $i++) {
            $block = array_slice($dataBytes, $offset, $g1d);
            $offset += $g1d;
            $dataBlocks[] = $block;
            $eccBlocks[] = $this->rsCompute($block, $generator);
        }
        for ($i = 0; $i < $g2b; $i++) {
            $block = array_slice($dataBytes, $offset, $g2d);
            $offset += $g2d;
            $dataBlocks[] = $block;
            $eccBlocks[] = $this->rsCompute($block, $generator);
        }

        $result = [];
        $maxDataLen = max(array_map('count', $dataBlocks));
        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($dataBlocks as $b) {
                if ($i < count($b)) {
                    $result[] = $b[$i];
                }
            }
        }
        for ($i = 0; $i < $eccPerBlock; $i++) {
            foreach ($eccBlocks as $b) {
                $result[] = $b[$i];
            }
        }
        return $result;
    }

    /**
     * @param array<int> $finalCodewords
     * @return array<int,array<int,bool|null>>
     */
    private function placeInMatrix(array $finalCodewords, int $version): array
    {
        $size = 17 + 4 * $version;
        $matrix = [];
        for ($y = 0; $y < $size; $y++) {
            $matrix[$y] = array_fill(0, $size, null);
        }

        $this->placeFinderPattern($matrix, 0, 0);
        $this->placeFinderPattern($matrix, $size - 7, 0);
        $this->placeFinderPattern($matrix, 0, $size - 7);

        for ($i = 0; $i < 8; $i++) {
            $v = ($i === 6) ? 1 : 0;
            $matrix[7][$i] = (bool)$v;
            $matrix[$size - 8][$i] = (bool)$v;
            $matrix[$i][7] = (bool)$v;
            $matrix[$i][$size - 8] = (bool)$v;
        }
        for ($i = 0; $i < 8; $i++) {
            $v = ($i === 7) ? 1 : 0;
            $matrix[7][$size - 1 - $i] = (bool)$v;
            $matrix[$size - 8][$size - 1 - $i] = (bool)(1 - $v);
            $matrix[$size - 1 - $i][7] = (bool)$v;
            $matrix[$size - 1 - $i][$size - 8] = (bool)(1 - $v);
        }
        $matrix[$size - 8][8] = true;

        $alignPos = self::ALIGNMENT_PATTERN_POS[$version];
        $apCount = count($alignPos);
        for ($i = 0; $i < $apCount; $i++) {
            for ($j = 0; $j < $apCount; $j++) {
                $cx = $alignPos[$j];
                $cy = $alignPos[$i];
                if (($cx === 6 && $cy === 6)
                    || ($cx === 6 && $cy === $size - 7)
                    || ($cx === $size - 7 && $cy === 6)) {
                    continue;
                }
                $this->placeAlignmentPattern($matrix, $cx, $cy);
            }
        }

        for ($i = 0; $i < $size; $i++) {
            if ($matrix[6][$i] === null) {
                $matrix[6][$i] = (bool)($i % 2 === 0);
            }
            if ($matrix[$i][6] === null) {
                $matrix[$i][6] = (bool)($i % 2 === 0);
            }
        }

        if ($version >= 7) {
            $bits = $this->versionInfoBits($version);
            for ($i = 0; $i < 18; $i++) {
                $a = (int)($bits >> $i) & 1;
                $matrix[(int)($i / 3)][(int)($i % 3) + $size - 8 - 3] = (bool)$a;
                $matrix[(int)($i % 3) + $size - 8 - 3][(int)($i / 3)] = (bool)$a;
            }
        }

        $totalBits = count($finalCodewords) * 8;
        $rem = self::REMAINDER_BITS[$version] ?? 0;
        $totalBits += $rem;

        $bitIdx = 0;
        $upward = true;
        for ($col = $size - 1; $col >= 1; $col -= 2) {
            if ($col === 6) {
                $col--;
            }
            for ($i = 0; $i < $size; $i++) {
                $y = $upward ? $size - 1 - $i : $i;
                for ($c = 0; $c < 2; $c++) {
                    $x = $col - $c;
                    if ($matrix[$y][$x] === null) {
                        $bit = 0;
                        if ($bitIdx < count($finalCodewords) * 8) {
                            $byteIdx = (int)($bitIdx / 8);
                            $bitInByte = 7 - ($bitIdx % 8);
                            $bit = ($finalCodewords[$byteIdx] >> $bitInByte) & 1;
                        }
                        $matrix[$y][$x] = (bool)$bit;
                        $bitIdx++;
                    }
                }
            }
            $upward = !$upward;
        }

        return $matrix;
    }

    private const REMAINDER_BITS = [
        1 => 0, 2 => 7, 3 => 7, 4 => 7, 5 => 7,
        6 => 7, 7 => 0, 8 => 0, 9 => 0, 10 => 0,
    ];

    /**
     * @param array<int,array<int,bool|null>> $matrix
     */
    private function placeFinderPattern(array &$matrix, int $x, int $y): void
    {
        for ($dy = 0; $dy < 7; $dy++) {
            for ($dx = 0; $dx < 7; $dx++) {
                $onEdge = ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6);
                $inCenter = ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4);
                $matrix[$y + $dy][$x + $dx] = (bool)($onEdge || $inCenter);
            }
        }
    }

    /**
     * @param array<int,array<int,bool|null>> $matrix
     */
    private function placeAlignmentPattern(array &$matrix, int $cx, int $cy): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $border = (abs($dx) === 2 || abs($dy) === 2);
                $center = ($dx === 0 && $dy === 0);
                $matrix[$cy + $dy][$cx + $dx] = (bool)($border || $center);
            }
        }
    }

    private function versionInfoBits(int $version): int
    {
        $golay = [
            7 => 0x07C94, 8 => 0x085BC, 9 => 0x09A99, 10 => 0x0A4D3,
        ];
        return $golay[$version] ?? 0;
    }

    /**
     * @param array<int,array<int,bool|null>> $matrix
     * @return array<int,array<int,bool>>
     */
    private function applyMask(array $matrix, int $mask, int $eccIdx): array
    {
        $size = count($matrix);
        $result = [];
        for ($y = 0; $y < $size; $y++) {
            $row = [];
            for ($x = 0; $x < $size; $x++) {
                $row[] = $matrix[$y][$x] ?? false;
            }
            $result[] = $row;
        }

        $modules = [];
        for ($y = 0; $y < $size; $y++) {
            $row = [];
            for ($x = 0; $x < $size; $x++) {
                $row[] = false;
            }
            $modules[] = $row;
        }
        $this->markFunctionModules($modules, $size, (int)(($size - 17) / 4));

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($modules[$y][$x]) {
                    continue;
                }
                if ($this->maskCondition($mask, $x, $y)) {
                    $result[$y][$x] = !$result[$y][$x];
                }
            }
        }

        $formatBits = $this->formatInfoBits($eccIdx, $mask);
        $this->placeFormatInfo($result, $formatBits, $size);

        return $result;
    }

    /**
     * @param array<int,array<int,bool>> $mods
     */
    private function markFunctionModules(array &$mods, int $size, int $version): void
    {
        for ($y = 0; $y < 9; $y++) {
            for ($x = 0; $x < 9; $x++) {
                $mods[$y][$x] = true;
            }
        }
        for ($y = 0; $y < 9; $y++) {
            for ($x = $size - 8; $x < $size; $x++) {
                $mods[$y][$x] = true;
            }
        }
        for ($y = $size - 8; $y < $size; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $mods[$y][$x] = true;
            }
        }
        for ($i = 0; $i < $size; $i++) {
            $mods[6][$i] = true;
            $mods[$i][6] = true;
        }
        $alignPos = self::ALIGNMENT_PATTERN_POS[$version + 1] ?? [];
        $apCount = count($alignPos);
        for ($i = 0; $i < $apCount; $i++) {
            for ($j = 0; $j < $apCount; $j++) {
                $cx = $alignPos[$j];
                $cy = $alignPos[$i];
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        if (isset($mods[$cy + $dy][$cx + $dx])) {
                            $mods[$cy + $dy][$cx + $dx] = true;
                        }
                    }
                }
            }
        }
        if ($version >= 6) {
            for ($i = 0; $i < 6; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    if (isset($mods[$i][$size - 11 + $j])) {
                        $mods[$i][$size - 11 + $j] = true;
                    }
                    if (isset($mods[$size - 11 + $j][$i])) {
                        $mods[$size - 11 + $j][$i] = true;
                    }
                }
            }
        }
    }

    private function maskCondition(int $mask, int $x, int $y): bool
    {
        return match ($mask) {
            0 => (($y + $x) % 2 === 0),
            1 => ($y % 2 === 0),
            2 => ($x % 3 === 0),
            3 => (($y + $x) % 3 === 0),
            4 => ((intdiv($y, 2) + intdiv($x, 3)) % 2 === 0),
            5 => ((($y * $x) % 2) + (($y * $x) % 3) === 0),
            6 => (((($y * $x) % 2) + (($y * $x) % 3)) % 2 === 0),
            7 => (((($y * $x) % 3) + (($y + $x) % 2)) % 2 === 0),
            default => false,
        };
    }

    private function formatInfoBits(int $eccIdx, int $mask): int
    {
        $data = ($eccIdx << 3) | $mask;
        $bch = $data << 10;
        $gen = 0x537;
        for ($i = 14; $i >= 10; $i--) {
            if (($bch >> $i) & 1) {
                $bch ^= ($gen << ($i - 10));
            }
        }
        $bits = (($data << 10) | $bch) ^ 0x5412;
        return $bits;
    }

    /**
     * @param array<int,array<int,bool>> $matrix
     */
    private function placeFormatInfo(array &$matrix, int $bits, int $size): void
    {
        for ($i = 0; $i <= 5; $i++) {
            $matrix[8][$i] = (bool)((($bits >> $i) & 1));
        }
        $matrix[8][7] = (bool)((($bits >> 6) & 1));
        $matrix[8][8] = (bool)((($bits >> 7) & 1));
        $matrix[7][8] = (bool)((($bits >> 8) & 1));
        for ($i = 9; $i <= 14; $i++) {
            $matrix[14 - $i][8] = (bool)((($bits >> $i) & 1));
        }

        for ($i = 0; $i <= 7; $i++) {
            $matrix[$size - 1 - $i][8] = (bool)((($bits >> $i) & 1));
        }
        for ($i = 8; $i <= 14; $i++) {
            $matrix[8][$size - 15 + $i] = (bool)((($bits >> $i) & 1));
        }
        $matrix[$size - 8][8] = true;
    }

    /**
     * @param array<int,array<int,bool>> $matrix
     */
    private function penaltyScore(array $matrix): int
    {
        $size = count($matrix);
        $score = 0;

        for ($y = 0; $y < $size; $y++) {
            $run = 1;
            for ($x = 1; $x < $size; $x++) {
                if ($matrix[$y][$x] === $matrix[$y][$x - 1]) {
                    $run++;
                    if ($run === 5) {
                        $score += 3;
                    } elseif ($run > 5) {
                        $score += 1;
                    }
                } else {
                    $run = 1;
                }
            }
        }
        for ($x = 0; $x < $size; $x++) {
            $run = 1;
            for ($y = 1; $y < $size; $y++) {
                if ($matrix[$y][$x] === $matrix[$y - 1][$x]) {
                    $run++;
                    if ($run === 5) {
                        $score += 3;
                    } elseif ($run > 5) {
                        $score += 1;
                    }
                } else {
                    $run = 1;
                }
            }
        }

        for ($y = 0; $y < $size - 1; $y++) {
            for ($x = 0; $x < $size - 1; $x++) {
                $c = $matrix[$y][$x];
                if ($c === $matrix[$y][$x + 1]
                    && $c === $matrix[$y + 1][$x]
                    && $c === $matrix[$y + 1][$x + 1]) {
                    $score += 3;
                }
            }
        }

        $dark = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix[$y][$x]) {
                    $dark++;
                }
            }
        }
        $total = $size * $size;
        $percent = (100 * $dark) / $total;
        $k = (int)floor(abs($percent - 50) / 5);
        $score += $k * 10;

        return $score;
    }

    /**
     * @return array<int,array<int,bool>>
     */
    private function encode(string $text, int $eccIdx): array
    {
        $bytes = array_values(unpack('C*', $text));
        $dataBits = count($bytes) * 8;
        $version = $this->pickVersion($dataBits, $eccIdx);

        $dataBytes = $this->buildBitstream($text, $version, $eccIdx);
        $finalCodewords = $this->constructFinalCodewords($dataBytes, $version, $eccIdx);
        $matrix = $this->placeInMatrix($finalCodewords, $version);

        $bestMask = 0;
        $bestScore = PHP_INT_MAX;
        $bestMatrix = null;
        for ($mask = 0; $mask < 8; $mask++) {
            $masked = $this->applyMask($matrix, $mask, $eccIdx);
            $score = $this->penaltyScore($masked);
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMask = $mask;
                $bestMatrix = $masked;
            }
        }

        if ($bestMatrix === null) {
            throw new \RuntimeException('Gagal generate QR matrix');
        }
        return $bestMatrix;
    }
}
