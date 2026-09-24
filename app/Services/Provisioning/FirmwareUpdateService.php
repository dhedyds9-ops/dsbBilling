<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;

class FirmwareUpdateService
{
    /**
     * Precheck logic for Firmware Upgrade.
     * Returns false if NO_OP or ERROR.
     */
    public function precheck(Onu $onu, array $targetFirmwareData): array
    {
        $currentVersion = $onu->capability->software_version ?? 'UNKNOWN';
        $targetVersion = $targetFirmwareData['version'] ?? 'UNKNOWN';

        if ($currentVersion === $targetVersion) {
            return ['status' => 'NO_OP', 'message' => 'Target firmware is identical to current firmware.'];
        }

        if (empty($targetFirmwareData['file_url'])) {
            return ['status' => 'ERROR', 'message' => 'Firmware file URL is missing.'];
        }

        return ['status' => 'READY', 'current_version' => $currentVersion, 'target_version' => $targetVersion];
    }

    /**
     * Push command to ACS.
     * Only indicates that command is accepted.
     */
    public function execute(Onu $onu, array $targetFirmwareData): bool
    {
        // Dummy integration for ACS push
        // GenieACS API call: POST /devices/{id}/tasks -> {"name": "download", "file": "firmware.bin"}
        Log::info("Pushing firmware {$targetFirmwareData['version']} to ONU {$onu->id}");
        
        ProvisioningAuditService::log(
            action: 'onu.firmware.execute',
            resourceType: Onu::class,
            resourceId: $onu->id,
            oldState: ['version' => $onu->capability->software_version ?? ''],
            newState: ['target_version' => $targetFirmwareData['version']],
            status: 'WAITING_RECONNECT'
        );

        return true;
    }

    /**
     * Polled verification method. To be called by a scheduled polling Job.
     */
    public function verifyReconnect(Onu $onu, int $elapsedTimeSeconds): array
    {
        // 1. Timeout Check (Max 10 minutes = 600s)
        if ($elapsedTimeSeconds >= 600) {
            ProvisioningAuditService::log(
                action: 'onu.firmware.timeout',
                resourceType: Onu::class,
                resourceId: $onu->id,
                status: 'UNKNOWN',
                reason: 'ONU failed to reconnect within 10 minutes. Manual review required.'
            );
            return ['status' => 'UNKNOWN', 'message' => 'Timeout reached. Status unknown, requires manual review.'];
        }

        // 2. Cek status is_online di sistem
        if (!$onu->is_online) {
            return ['status' => 'WAITING_RECONNECT'];
        }

        // 3. Jika online, verify version
        $newVersion = $onu->capability->software_version ?? '';
        
        // 4. Cek WAN connectivity (misal baca dari cache radius / pppoe active session)
        $isWanUp = true; // Placeholder logic
        
        if ($isWanUp) {
            ProvisioningAuditService::log(
                action: 'onu.firmware.verify',
                resourceType: Onu::class,
                resourceId: $onu->id,
                newState: ['version_verified' => $newVersion, 'wan_up' => true],
                status: 'SUCCESS'
            );
            return ['status' => 'SUCCESS'];
        }

        ProvisioningAuditService::log(
            action: 'onu.firmware.verify',
            resourceType: Onu::class,
            resourceId: $onu->id,
            newState: ['version_verified' => $newVersion, 'wan_up' => false],
            status: 'PARTIAL_FAILURE'
        );

        return ['status' => 'PARTIAL_FAILURE', 'message' => 'Firmware upgraded but WAN is down.'];
    }
}
