<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\OnuUnlockProfile;
use App\Models\AcsConfigurationProfile;
use App\Models\AcsFirmware;
use Illuminate\Support\Facades\Log;

class RemediationEngineService
{
    /**
     * Generate a Dry-Run plan for remediation.
     */
    public function generateRemediationPlan(Onu $onu, string $targetCapability, ?int $customerServiceId = null): array
    {
        Log::info("RemediationEngine: Generating plan for ONU {$onu->id}, target: {$targetCapability}");

        $identity = [
            'vendor' => $onu->vendor->name ?? 'UNKNOWN',
            'model' => $onu->model,
            'hardware_version' => $onu->hardware_version,
            'software_version' => $onu->firmware_version,
        ];

        // Ensure capabilities are discovered
        $capabilityRecord = $onu->capability;
        if (!$capabilityRecord || empty($capabilityRecord->capabilities)) {
            return [
                'status' => 'ERROR',
                'message' => 'Capabilities not discovered yet. Please run discovery first.'
            ];
        }

        $capabilities = is_string($capabilityRecord->capabilities) 
            ? json_decode($capabilityRecord->capabilities, true) 
            : $capabilityRecord->capabilities;

        $readinessKey = strtoupper($targetCapability) . '_READY';
        if (($capabilities['readiness'][$readinessKey] ?? false) === true) {
            return [
                'status' => 'NO_ACTION_NEEDED',
                'message' => "ONU is already ready for {$targetCapability}."
            ];
        }

        $plan = [
            'onu_id' => $onu->id,
            'customer_service_id' => $customerServiceId,
            'target_capability' => $targetCapability,
            'identity' => $identity,
            'steps' => [],
            'requires_approval' => false,
            'estimated_downtime' => false,
        ];

        // 1. Check for Configuration Remediation
        $configStep = $this->findConfigurationRemediation($identity, $targetCapability);
        if ($configStep) {
            $plan['steps'][] = $configStep;
            if ($configStep['approval_policy'] === 'APPROVAL_REQUIRED') {
                $plan['requires_approval'] = true;
            }
        }

        // 2. Check for Unlock Profile
        $unlockStep = $this->findUnlockRemediation($identity, $targetCapability);
        if ($unlockStep) {
            $plan['steps'][] = $unlockStep;
            if ($unlockStep['approval_policy'] === 'APPROVAL_REQUIRED') {
                $plan['requires_approval'] = true;
            }
            if ($unlockStep['reboot_required']) {
                $plan['estimated_downtime'] = true;
            }
        }

        // 3. Check for Firmware Upgrade (Only if config & unlock don't suffice or are strictly tied)
        // Usually Firmware is the last resort.
        $firmwareStep = $this->findFirmwareRemediation($identity, $targetCapability);
        if ($firmwareStep && empty($plan['steps'])) {
            $plan['steps'][] = $firmwareStep;
            if ($firmwareStep['approval_policy'] === 'APPROVAL_REQUIRED' || $firmwareStep['approval_policy'] === 'ADMIN_ONLY') {
                $plan['requires_approval'] = true;
            }
            $plan['estimated_downtime'] = true;
        }

        // If no steps found
        if (empty($plan['steps'])) {
            return [
                'status' => 'NO_REMEDIATION_AVAILABLE',
                'message' => "No valid remediation policy found for {$targetCapability} on this device model and firmware."
            ];
        }

        $plan['status'] = 'PLAN_GENERATED';

        // Add verification steps
        $plan['steps'][] = [
            'action' => 'REDISCOVER',
            'description' => 'Recalculate capability based on new parameters'
        ];
        $plan['steps'][] = [
            'action' => 'VERIFY',
            'description' => "Verify if {$targetCapability} is now AVAILABLE"
        ];

        // 4. Generate Comprehensive Plan Hash
        $hashData = [
            'identity' => $identity,
            'target_capability' => $targetCapability,
            'capability_state_hash' => $capabilityRecord->state_hash ?? '',
            'steps' => $plan['steps'],
            'requires_approval' => $plan['requires_approval'],
            'customer_service_id' => $customerServiceId
        ];
        $plan['plan_hash'] = hash('sha256', json_encode($hashData));
        $plan['expires_at'] = now()->addHours(1)->toIso8601String();

        return $plan;
    }

    protected function findConfigurationRemediation(array $identity, string $targetCapability): ?array
    {
        // Search in extended acs_configuration_profiles
        // In a real app, you'd match the vendor/model closely
        if (!class_exists(\App\Models\AcsConfigurationProfile::class)) {
            return null; // Fallback if model doesn't exist
        }
        $profile = AcsConfigurationProfile::where('type', 'REMEDIATION')
            ->where('vendor', $identity['vendor'])
            ->where('model', $identity['model'])
            ->where('status', 'active')
            ->first();

        if ($profile) {
            return [
                'action' => 'APPLY_CONFIG',
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'description' => "Apply base configuration to expose {$targetCapability}",
                'approval_policy' => $profile->approval_policy ?? 'AUTO',
            ];
        }

        return null;
    }

    protected function findUnlockRemediation(array $identity, string $targetCapability): ?array
    {
        $profile = OnuUnlockProfile::where('is_enabled', true)
            ->where('vendor', $identity['vendor'])
            ->where('model', $identity['model'])
            ->first();

        if ($profile) {
            // Validate Hardware Version if specified
            if ($profile->hardware_version && $profile->hardware_version !== $identity['hardware_version']) {
                return null;
            }

            return [
                'action' => 'UNLOCK',
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'method' => $profile->method,
                'risk_level' => $profile->risk_level,
                'reboot_required' => $profile->reboot_required,
                'approval_policy' => $profile->approval_policy,
                'description' => "Apply unlock profile via {$profile->method}",
            ];
        }

        return null;
    }

    protected function findFirmwareRemediation(array $identity, string $targetCapability): ?array
    {
        if (!class_exists(\App\Models\AcsFirmware::class)) {
            return null; 
        }

        $firmware = AcsFirmware::where('vendor_id', function($q) use ($identity) {
                // Simplified vendor resolution
                $q->select('id')->from('vendors')->where('name', $identity['vendor'])->limit(1);
            })
            ->where('model', $identity['model'])
            ->where('status', 'active')
            ->where('compatible_from_version', $identity['software_version']) // Example strict rule
            ->first();

        if ($firmware) {
            return [
                'action' => 'FIRMWARE_UPGRADE',
                'firmware_id' => $firmware->id,
                'version' => $firmware->version,
                'checksum' => $firmware->checksum_sha256,
                'risk_level' => $firmware->risk_level ?? 'HIGH',
                'approval_policy' => $firmware->approval_policy ?? 'APPROVAL_REQUIRED',
                'description' => "Upgrade firmware from {$identity['software_version']} to {$firmware->version}",
            ];
        }

        return null;
    }
}
