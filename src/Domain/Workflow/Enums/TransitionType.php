<?php

namespace Src\Domain\Workflow\Enums;

enum TransitionType: string
{
    case APPROVAL = 'approval';
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';
    case CONDITIONAL = 'conditional';
    case PARALLEL = 'parallel';
    case ROLLBACK = 'rollback';
    case ESCALATION = 'escalation';

    public function label(): string
    {
        return match($this) {
            self::APPROVAL => 'Requires Approval',
            self::AUTOMATIC => 'Automatic',
            self::MANUAL => 'Manual',
            self::CONDITIONAL => 'Conditional',
            self::PARALLEL => 'Parallel',
            self::ROLLBACK => 'Rollback',
            self::ESCALATION => 'Escalation',
        };
    }
}
