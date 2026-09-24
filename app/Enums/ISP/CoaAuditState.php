<?php

declare(strict_types=1);

namespace App\Enums\ISP;

/**
 * SSOT: COA Audit State Machine
 *
 * Alur normal:
 *   Created → Queued → Sent → Success
 *
 * Alur retry:
 *   Created → Sent → Timeout → Retry(sampai attempt < max) → Success / Failed / NAK
 */
enum CoaAuditState: string
{
    case Created = 'created';
    case Queued = 'queued';
    case Sent = 'sent';
    case Success = 'success';
    case Timeout = 'timeout';
    case Retry = 'retry';
    case Failed = 'failed';
    case Nak = 'nak';  // MikroTik / FreeRADIUS CoA NAK code 45

    public function isFinal(): bool
    {
        return in_array($this, [self::Success, self::Failed, self::Nak], true);
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Created => in_array($next, [self::Queued, self::Failed], true),
            self::Queued => in_array($next, [self::Sent, self::Failed], true),
            self::Sent => in_array($next, [self::Success, self::Timeout, self::Nak, self::Failed], true),
            self::Timeout => in_array($next, [self::Retry, self::Failed], true),
            self::Retry => in_array($next, [self::Sent, self::Failed], true),
            self::Success, self::Failed, self::Nak => false,
        };
    }
}
