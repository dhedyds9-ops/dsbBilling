<?php

namespace Src\Domain\Workforce\Enums;

enum TaskStatus: string {
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case CHECKLIST_IN_PROGRESS = 'checklist_in_progress';
    case PHOTO_IN_PROGRESS = 'photo_in_progress';
    case MATERIAL_CONSUMPTION = 'material_consumption';
    case SIGNATURE_PENDING = 'signature_pending';
    case QC_PENDING = 'qc_pending';
    case QC_APPROVED = 'qc_approved';
    case QC_REJECTED = 'qc_rejected';
    case ACTIVATION_PENDING = 'activation_pending';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
