<?php

namespace App\Services\ISP;

use App\Models\ISP\Pop;

class PopService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Pop::class;
    }
}
