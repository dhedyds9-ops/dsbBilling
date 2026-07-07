<?php

namespace Src\Domain\Workforce\Enums;

enum GeofenceStatus: string {
    case INSIDE = 'inside';
    case OUTSIDE = 'outside';
    case ENTERED = 'entered';
    case EXITED = 'exited';
}
