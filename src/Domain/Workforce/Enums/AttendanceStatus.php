<?php

namespace Src\Domain\Workforce\Enums;

enum AttendanceStatus: string
{
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case LATE = 'late';
    case ABSENT = 'absent';
    case PERMISSION = 'permission';
    case SICK = 'sick';
}
