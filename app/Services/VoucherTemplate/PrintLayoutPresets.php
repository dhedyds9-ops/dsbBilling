<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

use InvalidArgumentException;

/**
 * Menyediakan daftar preset ukuran kertas, grid cetak, dan generator
 * CSS untuk `@page` rule & layout `.voucher-grid` yang bisa langsung
 * disisipkan ke stylesheet cetak voucher.
 */
class PrintLayoutPresets
{
    public const PAPER_A4 = 'A4';
    public const PAPER_A5 = 'A5';
    public const PAPER_LETTER = 'LETTER';
    public const PAPER_THERMAL_58MM = 'THERMAL_58MM';
    public const PAPER_THERMAL_80MM = 'THERMAL_80MM';
    public const PAPER_CUSTOM = 'CUSTOM';

    public const GRID_2X5 = '2x5';
    public const GRID_3X5 = '3x5';
    public const GRID_3X6 = '3x6';
    public const GRID_4X6 = '4x6';
    public const GRID_4X7 = '4x7';

    /**
     * Mengembalikan seluruh preset: paper sizes, grids, dan default margins.
     *
     * Struktur output:
     * ```
     * [
     *   'papers' => [ 'A4' => ['width' => '210mm', 'height' => '297mm'], ... ],
     *   'grids'  => [ '2x5' => ['cols' => 2, 'rows' => 5, 'cards' => 10], ... ],
     *   'margins' => [
     *       'page' => ['top'=>5,'right'=>5,'bottom'=>5,'left'=>5],   // mm
     *       'card' => ['top'=>2,'right'=>2,'bottom'=>2,'left'=>2],   // mm
     *   ],
     * ]
     * ```
     *
     * @return array{
     *     papers: array<string, array{width:string,height:string}>,
     *     grids: array<string, array{cols:int,rows:int,cards:int}>,
     *     margins: array{
     *         page: array{top:int,right:int,bottom:int,left:int},
     *         card: array{top:int,right:int,bottom:int,left:int},
     *     },
     * }
     */
    public static function all(): array
    {
        return [
            'papers' => self::papers(),
            'grids' => self::grids(),
            'margins' => self::defaultMargins(),
        ];
    }

    /**
     * Menghasilkan string CSS siap pakai: `@page` rule + `.voucher-grid`
     * beserta child selector untuk grid & card layout.
     *
     * @param string               $paper         Nama paper preset (lihat konstanta PAPER_*)
     * @param string|null          $gridPreset    Nama grid preset (lihat konstanta GRID_*) atau null
     * @param array<string, mixed> $customMargins Override margin. Key yang didukung:
     *                                            `page_top`, `page_right`, `page_bottom`, `page_left`,
     *                                            `card_top`, `card_right`, `card_bottom`, `card_left`.
     *                                            Semua satuan mm (integer/float).
     *
     * @return string CSS dengan blok `@page`, `.voucher-grid`, dan `.voucher-card`
     *
     * @throws InvalidArgumentException Jika paper/grid tidak dikenal
     */
    public static function getCssForPreset(
        string $paper,
        ?string $gridPreset = null,
        array $customMargins = [],
    ): string {
        $paper = strtoupper($paper);
        $papers = self::papers();
        if (!isset($papers[$paper])) {
            throw new InvalidArgumentException(sprintf(
                'Paper preset tidak dikenal: "%s". Pilihan: %s',
                $paper,
                implode(', ', array_keys($papers)),
            ));
        }

        $paperSize = $papers[$paper];

        $grid = null;
        if ($gridPreset !== null && $gridPreset !== '') {
            $gridPreset = strtolower($gridPreset);
            $grids = self::grids();
            if (!isset($grids[$gridPreset])) {
                throw new InvalidArgumentException(sprintf(
                    'Grid preset tidak dikenal: "%s". Pilihan: %s',
                    $gridPreset,
                    implode(', ', array_keys($grids)),
                ));
            }
            $grid = $grids[$gridPreset];
        }

        $defaults = self::defaultMargins();
        $pageMargin = array_replace($defaults['page'], [
            'top'    => (int) ($customMargins['page_top']    ?? $defaults['page']['top']),
            'right'  => (int) ($customMargins['page_right']  ?? $defaults['page']['right']),
            'bottom' => (int) ($customMargins['page_bottom'] ?? $defaults['page']['bottom']),
            'left'   => (int) ($customMargins['page_left']   ?? $defaults['page']['left']),
        ]);
        $cardMargin = array_replace($defaults['card'], [
            'top'    => (int) ($customMargins['card_top']    ?? $defaults['card']['top']),
            'right'  => (int) ($customMargins['card_right']  ?? $defaults['card']['right']),
            'bottom' => (int) ($customMargins['card_bottom'] ?? $defaults['card']['bottom']),
            'left'   => (int) ($customMargins['card_left']   ?? $defaults['card']['left']),
        ]);

        return self::buildCss($paperSize, $pageMargin, $cardMargin, $grid);
    }

    /**
     * Daftar paper size yang didukung.
     *
     * @return array<string, array{width:string, height:string}>
     */
    public static function papers(): array
    {
        return [
            self::PAPER_A4           => ['width' => '210mm', 'height' => '297mm'],
            self::PAPER_A5           => ['width' => '148mm', 'height' => '210mm'],
            self::PAPER_LETTER       => ['width' => '216mm', 'height' => '279mm'],
            self::PAPER_THERMAL_58MM => ['width' => '58mm',  'height' => 'auto'],
            self::PAPER_THERMAL_80MM => ['width' => '80mm',  'height' => 'auto'],
            self::PAPER_CUSTOM       => ['width' => '0mm',   'height' => '0mm'],
        ];
    }

    /**
     * Daftar grid preset (khusus untuk kertas potong biasa A4/A5/Letter).
     *
     * @return array<string, array{cols:int, rows:int, cards:int}>
     */
    public static function grids(): array
    {
        return [
            self::GRID_2X5 => ['cols' => 2, 'rows' => 5, 'cards' => 10],
            self::GRID_3X5 => ['cols' => 3, 'rows' => 5, 'cards' => 15],
            self::GRID_3X6 => ['cols' => 3, 'rows' => 6, 'cards' => 18],
            self::GRID_4X6 => ['cols' => 4, 'rows' => 6, 'cards' => 24],
            self::GRID_4X7 => ['cols' => 4, 'rows' => 7, 'cards' => 28],
        ];
    }

    /**
     * Default margin untuk page dan card (satuan mm).
     *
     * @return array{
     *     page: array{top:int, right:int, bottom:int, left:int},
     *     card: array{top:int, right:int, bottom:int, left:int},
     * }
     */
    public static function defaultMargins(): array
    {
        return [
            'page' => ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5],
            'card' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2],
        ];
    }

    /**
     * Merakit string CSS akhir berdasarkan parameter yang sudah divalidasi.
     *
     * @param array{width:string, height:string}       $paperSize
     * @param array{top:int, right:int, bottom:int, left:int} $pageMargin
     * @param array{top:int, right:int, bottom:int, left:int} $cardMargin
     * @param array{cols:int, rows:int, cards:int}|null $grid
     */
    private static function buildCss(
        array $paperSize,
        array $pageMargin,
        array $cardMargin,
        ?array $grid,
    ): string {
        $isThermal = $paperSize['height'] === 'auto';
        $pageSizeRule = $paperSize['height'] === 'auto'
            ? sprintf('%s auto', $paperSize['width'])
            : sprintf('%s %s', $paperSize['width'], $paperSize['height']);

        $css = [];
        $css[] = '@page {';
        if ($isThermal) {
            $css[] = sprintf('  size: %s;', $pageSizeRule);
        }
        $css[] = sprintf(
            '  margin: %dmm %dmm %dmm %dmm;',
            $pageMargin['top'],
            $pageMargin['right'],
            $pageMargin['bottom'],
            $pageMargin['left'],
        );
        $css[] = '}';
        $css[] = '';
        $css[] = '@media print {';
        $css[] = '  body { margin: 0; padding: 0; }';
        $css[] = '  .no-print, .no-print * { display: none !important; }';
        $css[] = '}';
        $css[] = '';

        if ($isThermal) {
            $css[] = sprintf(
                '.voucher-grid { width: %s; display: flex; flex-direction: column; gap: 2mm; }',
                $paperSize['width'],
            );
        } elseif ($grid !== null) {
            $gap = max($cardMargin['top'], $cardMargin['left']);
            $css[] = '.voucher-grid {';
            $css[] = '  display: grid;';
            $css[] = sprintf('  grid-template-columns: repeat(%d, 1fr);', $grid['cols']);
            $css[] = sprintf('  grid-template-rows: repeat(%d, auto);', $grid['rows']);
            $css[] = sprintf('  gap: %dmm;', $gap);
            $css[] = '  width: 100%;';
            $css[] = '  box-sizing: border-box;';
            $css[] = '}';
        } else {
            $css[] = '.voucher-grid {';
            $css[] = '  display: flex;';
            $css[] = '  flex-wrap: wrap;';
            $css[] = '  gap: 2mm;';
            $css[] = '  width: 100%;';
            $css[] = '  box-sizing: border-box;';
            $css[] = '}';
        }

        $css[] = '';
        $css[] = '.voucher-card {';
        $css[] = '  box-sizing: border-box;';
        $css[] = '  page-break-inside: avoid;';
        $css[] = '  break-inside: avoid;';
        $css[] = sprintf(
            '  padding: %dmm %dmm %dmm %dmm;',
            $cardMargin['top'],
            $cardMargin['right'],
            $cardMargin['bottom'],
            $cardMargin['left'],
        );
        $css[] = '  overflow: hidden;';

        if ($grid !== null && !$isThermal) {
            $css[] = '  min-height: 0;';
            $css[] = '  display: flex;';
            $css[] = '  flex-direction: column;';
        }

        $css[] = '}';
        $css[] = '';
        $css[] = '.voucher-card-inner {';
        $css[] = '  width: 100%;';
        $css[] = '  height: 100%;';
        $css[] = '  box-sizing: border-box;';
        $css[] = '}';
        $css[] = '';
        $css[] = '.voucher-grid .voucher-card:nth-last-child(-n+1) { page-break-after: auto; }';

        return implode("\n", $css);
    }
}
