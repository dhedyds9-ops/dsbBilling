<?php

namespace App\Services\Adapters\Provisioning\Drivers;

use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;

class HsgqOltDriver extends BaseOltDriver
{
    public function getPonPortsStatus(): array
    {
        Log::info("HSGQ: getPonPortsStatus called for OLT {$this->olt->id}");
        return [];
    }

    public function getOnuRxPower(int $ponPort): array
    {
        return [];
    }

    public function discoverUnregisteredOnus(): array
    {
        return [];
    }

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool
    {
        Log::info("HSGQ: provisionOnu called for ONU {$onu->id}");
        return true;
    }

    public function setOnuAdminStatus(Onu $onu, string $status): bool
    {
        return true;
    }

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool
    {
        return true;
    }
}
