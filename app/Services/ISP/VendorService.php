<?php

namespace App\Services\ISP;

use App\Models\ISP\Vendor;

class VendorService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Vendor::class;
    }
}
