<?php

namespace App\Services\ISP;

use App\Models\ISP\DnsServer;

class DnsServerService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return DnsServer::class;
    }
}
