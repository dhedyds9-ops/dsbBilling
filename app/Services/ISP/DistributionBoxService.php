<?php

namespace App\Services\ISP;

use App\Models\ISP\DistributionBox;

class DistributionBoxService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return DistributionBox::class;
    }
}
