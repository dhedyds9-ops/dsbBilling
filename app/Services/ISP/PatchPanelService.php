<?php

namespace App\Services\ISP;

use App\Models\ISP\PatchPanel;

class PatchPanelService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return PatchPanel::class;
    }
}
