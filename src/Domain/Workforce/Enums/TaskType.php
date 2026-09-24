<?php

namespace Src\Domain\Workforce\Enums;

enum TaskType: string {
    case INSTALLATION = 'installation';
    case MAINTENANCE = 'maintenance';
    case TROUBLESHOOTING = 'troubleshooting';
}
