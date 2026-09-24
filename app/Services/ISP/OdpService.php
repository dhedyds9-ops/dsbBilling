<?php

namespace App\Services\ISP;

use App\Models\ISP\Odp;

class OdpService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Odp::class;
    }
}
