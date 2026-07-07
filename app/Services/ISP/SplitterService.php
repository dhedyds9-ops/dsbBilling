<?php

namespace App\Services\ISP;

use App\Models\ISP\Splitter;

class SplitterService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Splitter::class;
    }
}
