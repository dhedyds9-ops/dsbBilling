<?php

namespace App\Services\ISP;

use App\Models\ISP\FiberCore;

class FiberCoreService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return FiberCore::class;
    }
}
