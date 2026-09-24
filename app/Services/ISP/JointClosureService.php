<?php

namespace App\Services\ISP;

use App\Models\ISP\JointClosure;

class JointClosureService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return JointClosure::class;
    }
}
