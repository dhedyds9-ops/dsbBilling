<?php

namespace App\Services\ISP;

use App\Models\ISP\OnuPort;

class OnuPortService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return OnuPort::class;
    }
}
