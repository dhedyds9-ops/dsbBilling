<?php

namespace Src\Domain\Workforce\ValueObjects;

enum AssignmentPriority: int {
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
    case CRITICAL = 4;
    case EMERGENCY = 5;

    public function label(): string {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
            self::EMERGENCY => 'Emergency',
        };
    }
}
