<?php

namespace App\Services\ISP;

use App\Models\ISP\FiberCable;

class FiberCableService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return FiberCable::class;
    }
}
