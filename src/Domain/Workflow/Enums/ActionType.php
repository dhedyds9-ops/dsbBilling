<?php

namespace Src\Domain\Workflow\Enums;

enum ActionType: string
{
    case NOTIFY = 'notify';
    case EMAIL = 'email';
    case SMS = 'sms';
    case WEBHOOK = 'webhook';
    case API_CALL = 'api_call';
    case UPDATE_FIELD = 'update_field';
    case CREATE_RECORD = 'create_record';
    case UPDATE_RECORD = 'update_record';
    case DELETE_RECORD = 'delete_record';
    case ASSIGN_TASK = 'assign_task';
    case ESCALATE = 'escalate';
    case APPROVE = 'approve';
    case REJECT = 'reject';
    case CUSTOM = 'custom';
    case ROLLBACK = 'rollback';
    case RETRY = 'retry';
    case PAUSE = 'pause';
    case RESUME = 'resume';

    public function label(): string
    {
        return match($this) {
            self::NOTIFY => 'Send Notification',
            self::EMAIL => 'Send Email',
            self::SMS => 'Send SMS',
            self::WEBHOOK => 'Trigger Webhook',
            self::API_CALL => 'Call API',
            self::UPDATE_FIELD => 'Update Field',
            self::CREATE_RECORD => 'Create Record',
            self::UPDATE_RECORD => 'Update Record',
            self::DELETE_RECORD => 'Delete Record',
            self::ASSIGN_TASK => 'Assign Task',
            self::ESCALATE => 'Escalate',
            self::APPROVE => 'Auto Approve',
            self::REJECT => 'Auto Reject',
            self::CUSTOM => 'Custom Action',
            self::ROLLBACK => 'Rollback',
            self::RETRY => 'Retry',
            self::PAUSE => 'Pause Workflow',
            self::RESUME => 'Resume Workflow',
        };
    }
}
