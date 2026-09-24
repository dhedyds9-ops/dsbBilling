<?php

declare(strict_types=1);

namespace App\Enums\ISP;

enum CoaType: string
{
    case Suspend = 'suspend';
    case Reactivate = 'reactivate';
    case BandwidthChange = 'bandwidth_change';
    case RebalanceProfile = 'rebalance_profile';
    case AdminForceDisconnect = 'admin_force_disconnect';
}
