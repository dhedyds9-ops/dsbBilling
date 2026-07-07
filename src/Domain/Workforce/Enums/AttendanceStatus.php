<?php

namespace Src\Domain\Workforce\Enums;

enum AttendanceStatus: string {
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case ABSENT = 'absent';
    case LATE = 'late';
    case EARLY_LEAVE = 'early_leave';
}
