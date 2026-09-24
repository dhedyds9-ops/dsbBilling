<?php

declare(strict_types=1);

namespace App\Enums\ISP;

enum VoucherTemplateSize: string
{
    case A4 = 'A4';
    case A5 = 'A5';
    case Letter = 'LETTER';
    case Thermal58mm = 'THERMAL_58MM';
    case Thermal80mm = 'THERMAL_80MM';
    case Custom = 'CUSTOM';
}
