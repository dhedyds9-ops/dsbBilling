<?php

namespace Src\Domain\Workforce\Enums;

enum QCStatus: string {
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case PASSED = 'passed';
    case FAILED = 'failed';
}
