<?php

namespace App\Services\ISP;

use App\Models\ISP\RadiusServer;

class RadiusServerService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return RadiusServer::class;
    }
}
