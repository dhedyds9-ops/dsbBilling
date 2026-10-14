<?php

namespace Src\Domain\GIS\Enums;

enum CoverageStatus: string {
    case ACTIVE = 'active';
    case PLANNED = 'planned';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}
