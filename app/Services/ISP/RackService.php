<?php

namespace App\Services\ISP;

use App\Models\ISP\Rack;

class RackService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Rack::class;
    }
}
