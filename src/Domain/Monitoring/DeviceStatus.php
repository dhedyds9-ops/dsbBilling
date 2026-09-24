<?php

namespace Src\Domain\Monitoring;

enum DeviceStatus: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case MAINTENANCE = 'maintenance';
    case UNKNOWN = 'unknown';
}
