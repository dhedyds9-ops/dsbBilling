<?php

declare(strict_types=1);

namespace App\Enums\ISP;

/**
 * SSOT: Radius Session State Machine
 *
 * Alur normal:
 *   Connecting → Authenticated → Online → Disconnect → Stop
 *
 * Alur isolir via COA:
 *   Online → SuspendApplied → Reconnect → Online
 *
 * @see app/Services/ISP/Radius/RadiusSessionStateMachine.php
 */
enum RadiusSessionState: string
{
    case Connecting = 'connecting';
    case Authenticated = 'authenticated';
    case AuthorizationFailed = 'authz_failed';
    case Online = 'online';
    case SuspendPending = 'suspend_pending';
    case SuspendApplied = 'suspend_applied';
    case SuspendFailed = 'suspend_failed';
    case ReactivatePending = 'reactivate_pending';
    case Reconnect = 'reconnect';
    case Disconnecting = 'disconnecting';
    case Stop = 'stop';
    case Stale = 'stale';
    case Error = 'error';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Connecting => in_array($next, [self::Authenticated, self::AuthorizationFailed, self::Error, self::Stale], true),
            self::Authenticated => in_array($next, [self::Online, self::AuthorizationFailed, self::Error], true),
            self::Online => in_array($next, [
                self::SuspendPending, self::SuspendApplied, self::Reconnect,
                self::Disconnecting, self::Stale, self::ReactivatePending,
            ], true),
            self::SuspendPending => in_array($next, [self::SuspendApplied, self::SuspendFailed, self::Error], true),
            self::SuspendApplied => in_array($next, [self::ReactivatePending, self::Reconnect, self::Disconnecting], true),
            self::SuspendFailed => in_array($next, [self::Online, self::Disconnecting, self::Error], true),
            self::ReactivatePending => in_array($next, [self::Online, self::SuspendApplied, self::Error], true),
            self::Reconnect => in_array($next, [self::Online, self::Authenticated, self::Connecting, self::Stop], true),
            self::Disconnecting => in_array($next, [self::Stop, self::Error], true),
            self::Stop, self::Stale, self::Error, self::AuthorizationFailed => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Connecting => 'Connecting',
            self::Authenticated => 'Authenticated',
            self::AuthorizationFailed => 'Authorization Failed',
            self::Online => 'Online',
            self::SuspendPending => 'Suspend Pending',
            self::SuspendApplied => 'Suspend Applied (via COA)',
            self::SuspendFailed => 'Suspend Failed (fallback ke disconnect)',
            self::ReactivatePending => 'Reactivate Pending',
            self::Reconnect => 'Reconnecting',
            self::Disconnecting => 'Disconnecting',
            self::Stop => 'Session Stopped',
            self::Stale => 'Stale (died without Stop)',
            self::Error => 'Session Error',
        };
    }
}
