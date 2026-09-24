<?php

namespace App\Services\Provisioning;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use Illuminate\Support\Facades\Log;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class ServiceProvisioningEngine
{
    protected CapabilityDiscoveryService $discoveryService;
    protected RemediationEngineService $remediationEngine;
    protected GenieACSDriver $acsDriver;

    public function __construct(
        CapabilityDiscoveryService $discoveryService,
        RemediationEngineService $remediationEngine,
        GenieACSDriver $acsDriver
    ) {
        $this->discoveryService = $discoveryService;
        $this->remediationEngine = $remediationEngine;
        $this->acsDriver = $acsDriver;
    }

    /**
     * Entry point to provision a CustomerService onto an ONU.
     */
    public function provisionService(CustomerService $customerService): array
    {
        $onu = $customerService->onu;
        if (!$onu) {
            return ['status' => 'ERROR', 'message' => 'No ONU associated with this customer service.'];
        }

        Log::info("ServiceProvisioningEngine: Starting provisioning for CS {$customerService->id} on ONU {$onu->id}");

        // 1. Determine Required Capability
        // For standard internet service, we need either PPPOE or BRIDGE or DHCP capability
        // Let's assume standard is PPPOE for router mode, BRIDGE for bridge mode.
        // We look at network profile or attributes. For simplicity, we require PPPOE.
        
        $targetCapability = 'PPPOE'; 
        if (($customerService->attributes['connection_type'] ?? '') === 'BRIDGE') {
            $targetCapability = 'BRIDGE';
        }

        // 2. Discovery & Capability Check
        $discoveryResult = $this->discoveryService->discover($onu);
        if (isset($discoveryResult['status']) && $discoveryResult['status'] === 'DISCOVERY_FAILED') {
            return ['status' => 'ERROR', 'message' => 'Failed to reach ONU for capability discovery. Is it online?'];
        }

        $readinessKey = $targetCapability . '_READY';
        $isReady = $discoveryResult['readiness'][$readinessKey] ?? false;

        // 3. Evaluate Readiness
        if ($isReady) {
            // 4A. Ready -> Provision Service Now
            return $this->executeProvisioning($customerService, $onu, $targetCapability);
        } else {
            // 4B. Not Ready -> Generate Remediation Plan
            $plan = $this->remediationEngine->generateRemediationPlan($onu, $targetCapability, $customerService->id);

            if ($plan['status'] === 'NO_REMEDIATION_AVAILABLE') {
                return [
                    'status' => 'MANUAL_REVIEW',
                    'message' => "ONU does not support {$targetCapability} and no remediation policy is available."
                ];
            }

            return [
                'status' => 'REMEDIATION_REQUIRED',
                'message' => "ONU is not ready for {$targetCapability}. Remediation is required before provisioning.",
                'target_capability' => $targetCapability,
                'customer_service_id' => $customerService->id,
                'remediation_plan' => $plan
            ];
        }
    }

    /**
     * The actual creation of WAN / Bridge via TR-069
     */
    protected function executeProvisioning(CustomerService $customerService, Onu $onu, string $targetCapability): array
    {
        Log::info("ServiceProvisioningEngine: Executing {$targetCapability} provisioning on ONU {$onu->id}");

        try {
            // Idempotency: Check if already PROVISIONED or PENDING_VERIFICATION
            if (in_array($customerService->tr069_status, ['PROVISIONED', 'PENDING_VERIFICATION']) && $customerService->service_status === 'ACTIVE') {
                return [
                    'status' => 'SUCCESS',
                    'message' => 'Service is already provisioned and active. No changes made.'
                ];
            }

            // Update to PROVISIONING
            $customerService->update(['service_status' => 'PROVISIONING']);

            // Get credentials from the database
            $username = $customerService->username;
            $password = $customerService->password;
            $vlanId = $customerService->attributes['vlan_id'] ?? null;

            // Trigger the smart Multi-Vendor WAN setup via GenieACS!
            if ($username && $password && $onu->genieacs_device_id) {
                try {
                    $this->acsDriver->provisionPppoe(
                        $onu->genieacs_device_id, 
                        $username, 
                        $password, 
                        $vlanId
                    );
                    Log::info("Successfully sent PPPoE auto-config for {$username} to GenieACS.");
                } catch (\Exception $e) {
                    Log::error("Failed to provision PPPoE: " . $e->getMessage());
                    return [
                        'status' => 'ERROR',
                        'message' => 'Gagal mengirim konfigurasi PPPoE ke ONT via TR-069: ' . $e->getMessage()
                    ];
                }
            } else {
                Log::warning("Skipping ACS PPPoE provision: Missing username/password or device_id.");
            }

            // Crucial: Set to PENDING_VERIFICATION, not ACTIVE immediately
            $customerService->update([
                'tr069_status' => 'PENDING_VERIFICATION'
            ]);

            // Usually, here we'd trigger a verify job or webhook from ACS
            
            return [
                'status' => 'SUCCESS',
                'message' => "Service successfully provisioned via {$targetCapability}. Awaiting verification."
            ];
        } catch (\Exception $e) {
            Log::error("Provisioning execution failed: " . $e->getMessage());
            $customerService->update(['service_status' => 'FAILED']);
            return ['status' => 'ERROR', 'message' => 'TR-069 Provisioning failed: ' . $e->getMessage()];
        }
    }
}
