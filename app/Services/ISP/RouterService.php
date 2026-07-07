<?php

namespace App\Services\ISP;

use App\Models\ISP\Router;

class RouterService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Router::class;
    }
}
