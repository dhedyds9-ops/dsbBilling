<?php

namespace App\Services\ISP;

use App\Models\ISP\Ups;

class UpsService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Ups::class;
    }
}
