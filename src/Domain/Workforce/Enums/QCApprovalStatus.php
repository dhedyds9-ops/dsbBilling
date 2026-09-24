<?php

namespace Src\Domain\Workforce\Enums;

enum QCApprovalStatus: string {
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
