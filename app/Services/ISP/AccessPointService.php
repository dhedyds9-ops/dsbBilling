<?php

namespace App\Services\ISP;

use App\Models\ISP\AccessPoint;

class AccessPointService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return AccessPoint::class;
    }
}
