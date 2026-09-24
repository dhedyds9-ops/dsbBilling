<?php

namespace App\Services\Adapters\Provisioning\Contracts;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;

interface OltDriverInterface
{
    public function __construct(Olt $olt);

    public function getSystemInfo(): array;

    public function getPonPortsStatus(): array;

    public function getOnuRxPower(int $ponPort): array;

    public function getTemperature(): float;

    public function executeCommand(string $command, string $mode = 'telnet'): string|bool;

    public function discoverUnregisteredOnus(): array;

    public function provisionOnu(Onu $onu, string $serialNumber, int $ponPort, string $profile = 'default'): bool;

    public function setOnuAdminStatus(Onu $onu, string $status): bool;

    public function setOnuBandwidthLimit(Onu $onu, int $downloadMbps, int $uploadMbps): bool;

    public function rebootOlt(): bool;

    public function saveConfig(): bool;

    public function rebootOnu(Onu $onu): bool;

    public function getOnuSignal(Onu $onu): array;
}
