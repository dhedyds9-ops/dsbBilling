<?php

namespace Src\Domain\Workflow\Enums;

enum TriggerType: string
{
    case MANUAL = 'manual';
    case SCHEDULED = 'scheduled';
    case EVENT = 'event';
    case API = 'api';
    case WEBHOOK = 'webhook';
    case CONDITION = 'condition';
    case DATECHANGE = 'date_change';
    case USERACTION = 'user_action';

    public function label(): string
    {
        return match($this) {
            self::MANUAL => 'Manual Trigger',
            self::SCHEDULED => 'Scheduled',
            self::EVENT => 'Event Based',
            self::API => 'API Call',
            self::WEBHOOK => 'Webhook',
            self::CONDITION => 'Condition Met',
            self::DATECHANGE => 'Date Change',
            self::USERACTION => 'User Action',
        };
    }
}
