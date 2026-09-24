<?php

namespace App\Services\ISP;

use App\Models\ISP\Olt;

class OltService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Olt::class;
    }
}
