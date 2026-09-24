<?php

declare(strict_types=1);

namespace App\Enums\ISP;

enum VoucherPrintPreset: string
{
    case Grid2x5 = '2x5';
    case Grid3x5 = '3x5';
    case Grid3x6 = '3x6';
    case Grid4x6 = '4x6';
    case Grid4x7 = '4x7';
}
