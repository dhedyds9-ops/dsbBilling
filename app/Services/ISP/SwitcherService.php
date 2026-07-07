<?php

namespace App\Services\ISP;

use App\Models\ISP\Switcher;

class SwitcherService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Switcher::class;
    }
}
