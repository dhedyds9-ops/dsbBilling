<?php

namespace Src\Domain\Workforce\Enums;

enum SyncTaskStatus: string {
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case CONFLICT = 'conflict';
}
