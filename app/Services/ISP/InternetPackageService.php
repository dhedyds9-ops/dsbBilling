<?php

namespace App\Services\ISP;

use App\Models\ISP\InternetPackage;

class InternetPackageService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return InternetPackage::class;
    }
}
