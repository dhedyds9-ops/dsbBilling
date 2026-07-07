<?php

namespace App\Services\ISP;

use App\Models\ISP\DistributionLink;

class DistributionLinkService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return DistributionLink::class;
    }
}
