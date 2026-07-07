<?php

namespace App\Services\ISP;

use App\Models\ISP\NasDevice;

class NasDeviceService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return NasDevice::class;
    }
}
