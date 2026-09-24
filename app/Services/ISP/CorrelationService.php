<?php

namespace App\Services\ISP;

use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\ISP\DeviceCorrelation;
use App\Models\ISP\Onu;
use App\Models\ISP\PPPoEUser;
use Illuminate\Support\Facades\Log;

class CorrelationService
{
    public function evaluateOnu(Onu $onu): void
    {
        $sn = $onu->serial_number;
        $mac = $onu->mac_address;
        
        $acsDevices = collect();
        if ($sn) {
            $acsDevices = ACSDevice::where('serial_number', $sn)->get();
            if ($acsDevices->isEmpty()) {
                $acsDevices = ACSDevice::where('uuid', $sn)->get();
            }
        }
        
        if ($acsDevices->isEmpty() && $mac) {
            $acsDevices = ACSDevice::where('mac_address', $mac)->get();
        }

        if ($acsDevices->isEmpty()) {
            return;
        }

        foreach ($acsDevices as $acsDevice) {
            $this->evaluatePair($onu, $acsDevice);
        }
    }

    public function evaluateAcsDevice(ACSDevice $acsDevice): void
    {
        $sn = $acsDevice->serial_number ?? $acsDevice->uuid;
        $mac = $acsDevice->mac_address;

        $onus = collect();
        if ($sn) {
            $onus = Onu::where('serial_number', $sn)->get();
        }
        if ($onus->isEmpty() && $mac) {
            $onus = Onu::where('mac_address', $mac)->get();
        }

        foreach ($onus as $onu) {
            $this->evaluatePair($onu, $acsDevice);
        }
    }

    protected function evaluatePair(Onu $onu, ACSDevice $acsDevice): void
    {
        $score = 0;
        $methods = [];
        $candidateCustomerId = null;

        // 1. Serial Number match
        $onuSn = strtoupper(trim($onu->serial_number ?? ''));
        $acsSn = strtoupper(trim($acsDevice->serial_number ?? $acsDevice->uuid ?? ''));
        if ($onuSn !== '' && $onuSn === $acsSn) {
            $score += 100;
            $methods[] = 'serial_number';
        }

        // 2. Persistent GenieACS Device ID
        if ($acsDevice->onu_id === $onu->id) {
            $score += 100;
            $methods[] = 'acs_device_onu_id';
        }

        // 3. CPE MAC match & PPPoE username match
        $onuMac = strtoupper(trim($onu->mac_address ?? ''));
        $acsMac = strtoupper(trim($acsDevice->mac_address ?? ''));
        if ($onuMac !== '' && $onuMac === $acsMac) {
            $score += 80;
            $methods[] = 'mac_address';
        }

        // Try to find if this ONU or ACS device is already linked to a customer service
        // via PPPoE MAC or username
        $pppoeUserByMac = null;
        if ($onuMac) {
            $pppoeUserByMac = PPPoEUser::where('caller_id', $onuMac)->orWhere('mac_address', $onuMac)->first();
        } elseif ($acsMac) {
            $pppoeUserByMac = PPPoEUser::where('caller_id', $acsMac)->orWhere('mac_address', $acsMac)->first();
        }

        if ($pppoeUserByMac && $pppoeUserByMac->customer_service_id) {
            $score += 80;
            $methods[] = 'pppoe_mac_match';
            $candidateCustomerId = $pppoeUserByMac->customer_service_id;
        }

        // IP Correlation (heuristic)
        // If they share the same IP, add 30
        if ($acsDevice->ip_address && $onu->ip_address && $acsDevice->ip_address === $onu->ip_address) {
            $score += 30;
            $methods[] = 'ip_correlation';
        }
        
        // Capping score to 100 for simplicity if it exceeds
        if ($score > 100) $score = 100;

        if ($score === 0) {
            return;
        }

        $status = 'rejected';
        if ($score >= 90) {
            $status = 'auto_bind';
        } elseif ($score >= 70) {
            $status = 'candidate';
        }

        $correlation = DeviceCorrelation::updateOrCreate(
            [
                'onu_id' => $onu->id,
                'genieacs_device_id' => $acsSn,
            ],
            [
                'customer_service_id' => $candidateCustomerId, // Can be null
                'match_method' => implode(',', $methods),
                'confidence_score' => $score,
                'matched_by' => 'auto',
                'matched_at' => now(),
                'status' => $status,
            ]
        );

        if ($status === 'auto_bind' && $candidateCustomerId) {
            $this->performBind($correlation, $candidateCustomerId);
        } elseif ($status === 'auto_bind' && !$candidateCustomerId) {
            // It's a match between ONU and GenieACS, but no customer yet.
            // Still an auto_bind for the device layer.
        }
    }

    protected function performBind(DeviceCorrelation $correlation, int $customerServiceId): void
    {
        $cs = CustomerService::find($customerServiceId);
        if (!$cs) return;

        $updated = false;
        if ($cs->onu_id !== $correlation->onu_id) {
            $cs->onu_id = $correlation->onu_id;
            $updated = true;
        }

        $attrs = $cs->attributes ?? [];
        if (($attrs['genieacs_device_id'] ?? '') !== $correlation->genieacs_device_id) {
            $attrs['genieacs_device_id'] = $correlation->genieacs_device_id;
            $cs->attributes = $attrs;
            $updated = true;
        }

        if ($updated) {
            $cs->save();
            Log::info("CorrelationService: Auto-bound CustomerService {$customerServiceId} to ONU {$correlation->onu_id} and ACS {$correlation->genieacs_device_id} (Score: {$correlation->confidence_score})");
            
            // Trigger state evaluation
            app(UnifiedDeviceStateEngine::class)->evaluateAndSave($cs);
        }
    }
}
