<?php

namespace App\Services\ISP;

use App\Models\ISP\BackboneLink;

class BackboneLinkService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return BackboneLink::class;
    }
}
