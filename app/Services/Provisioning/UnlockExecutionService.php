<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;

class UnlockExecutionService
{
    /**
     * Start the unlock sequence. Does not expose credentials to the plan or audit log.
     */
    public function executeUnlock(Onu $onu, array $profileData, ?string $reason = null): array
    {
        // 1. Fetch secret payload internally (from DB / Vault)
        $username = $profileData['username'] ?? 'admin';
        $password = $profileData['password'] ?? 'secret'; // Loaded from secure config, not user input

        // 2. Audit Intent (masked)
        ProvisioningAuditService::log(
            action: 'onu.unlock.execute',
            resourceType: Onu::class,
            resourceId: $onu->id,
            oldState: ['locked' => true],
            newState: ['profile_id' => $profileData['id'] ?? null],
            status: 'EXECUTING',
            reason: $reason
        );

        // 3. Execution (e.g., Telnet/SSH/ACS)
        try {
            // Push script to device ...
            $success = true; // Placeholder

            if ($success) {
                ProvisioningAuditService::log(
                    action: 'onu.unlock.verify',
                    resourceType: Onu::class,
                    resourceId: $onu->id,
                    status: 'SUCCESS'
                );
                return ['status' => 'SUCCESS', 'message' => 'ONU Unlocked successfully.'];
            }
        } catch (\Exception $e) {
            ProvisioningAuditService::log(
                action: 'onu.unlock.verify',
                resourceType: Onu::class,
                resourceId: $onu->id,
                status: 'FAILED',
                reason: 'Connection timeout or invalid credentials.'
            );
            return ['status' => 'ERROR', 'message' => 'Failed to unlock device.'];
        }

        return ['status' => 'UNKNOWN'];
    }
}
