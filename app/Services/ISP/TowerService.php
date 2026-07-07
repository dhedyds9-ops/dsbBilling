<?php

namespace App\Services\ISP;

use App\Models\ISP\Tower;

class TowerService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Tower::class;
    }
}
