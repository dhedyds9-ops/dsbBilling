<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;

class OnuService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Onu::class;
    }
}
