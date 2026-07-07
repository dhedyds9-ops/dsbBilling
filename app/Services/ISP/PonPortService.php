<?php

namespace App\Services\ISP;

use App\Models\ISP\PonPort;

class PonPortService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return PonPort::class;
    }
}
