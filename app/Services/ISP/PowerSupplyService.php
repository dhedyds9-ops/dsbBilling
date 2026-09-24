<?php

namespace App\Services\ISP;

use App\Models\ISP\PowerSupply;

class PowerSupplyService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return PowerSupply::class;
    }
}
