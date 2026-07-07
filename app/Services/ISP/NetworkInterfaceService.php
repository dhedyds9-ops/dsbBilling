<?php

namespace App\Services\ISP;

use App\Models\ISP\NetworkInterface;

class NetworkInterfaceService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return NetworkInterface::class;
    }
}
