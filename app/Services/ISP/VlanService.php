<?php

namespace App\Services\ISP;

use App\Models\ISP\Vlan;

class VlanService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Vlan::class;
    }
}
