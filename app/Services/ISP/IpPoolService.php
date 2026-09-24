<?php

namespace App\Services\ISP;

use App\Models\ISP\IpPool;

class IpPoolService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return IpPool::class;
    }
}
