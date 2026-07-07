<?php

namespace Src\Domain\Workforce\Enums;

enum TechnicianStatus: string {
    case AVAILABLE = 'available';
    case BUSY = 'busy';
    case OFFLINE = 'offline';
    case ON_LEAVE = 'on_leave';
    case INACTIVE = 'inactive';
}
