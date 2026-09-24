<?php

namespace App\Services\ISP;

use App\Models\ISP\Odc;

class OdcService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Odc::class;
    }
}
