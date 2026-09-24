<?php

declare(strict_types=1);

namespace App\Enums\ISP;

enum VoucherTemplateCategory: string
{
    case Classic = 'classic';
    case Modern = 'modern';
    case Premium = 'premium';
    case Gaming = 'gaming';
    case Wifi = 'wifi';
    case Qr = 'qr';
    case Thermal = 'thermal';
    case InkSaver = 'ink_saver';
    case Custom = 'custom';
}
