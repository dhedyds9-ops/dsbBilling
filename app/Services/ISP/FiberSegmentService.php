<?php

namespace App\Services\ISP;

use App\Models\ISP\FiberSegment;

class FiberSegmentService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return FiberSegment::class;
    }
}
