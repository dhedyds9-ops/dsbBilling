<?php

namespace App\Services\ISP;

use App\Models\Customer\CustomerService;
use App\Models\ISP\OnlineSession;
use App\Models\ACS\ACSDevice;
use App\Models\ISP\Onu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UnifiedDeviceStateEngine
{
    public function evaluateAllActiveServices(): void
    {
        $services = CustomerService::where('status', 'active')->cursor();
        foreach ($services as $service) {
            $this->evaluateAndSave($service);
        }
    }

    public function evaluateAndSave(CustomerService $service): void
    {
        // 1. Evaluate Optical Status
        $opticalStatus = 'unknown';
        $onu = $service->onu;
        if ($onu) {
            if ($onu->status === 'active' && $onu->is_online) {
                $opticalStatus = 'online';
            } else {
                $rxPower = $onu->rx_power_dbm;
                $threshold = config('olt-drivers.thresholds.onu_rx_power_critical_low', -28.0);
                if ($rxPower !== null && $rxPower <= $threshold) {
                    $opticalStatus = 'los';
                } else {
                    $opticalStatus = 'offline';
                }
            }
        }

        // 2. Evaluate TR-069 Status
        $tr069Status = 'unknown';
        $deviceId = null;
        if ($onu) {
            $deviceId = $onu->genieacsDeviceId;
        } elseif (isset($service->attributes['genieacs_device_id'])) {
            $deviceId = $service->attributes['genieacs_device_id'];
        }

        if ($deviceId) {
            $acsDevice = ACSDevice::where('serial_number', $deviceId)
                ->orWhere('mac_address', $deviceId)
                ->orWhere('uuid', $deviceId)
                ->first();
            
            if ($acsDevice) {
                $tr069Status = $acsDevice->status; // Will be refined in Phase 2 with STALE logic
            }
        }

        // 3. Evaluate Service (PPPoE/Hotspot) Status
        $serviceStatus = 'unknown';
        $session = null;
        if ($service->pppoeUser) {
            $session = OnlineSession::where('pppoe_user_id', $service->pppoeUser->id)->first();
        } elseif ($service->hotspotUser) {
            $session = OnlineSession::where('hotspot_user_id', $service->hotspotUser->id)->first();
        } elseif ($service->username) {
            $session = OnlineSession::where('username', $service->username)->first();
        }
        
        if ($session) {
            $serviceStatus = 'online';
        } else {
            // Is it isolated?
            if ($service->status === 'suspended') {
                $serviceStatus = 'isolated';
            } elseif ($service->status === 'active') {
                $serviceStatus = 'offline';
            }
        }

        // 4. Determine Diagnostic Status
        $diagnosticStatus = 'unknown';
        
        if ($opticalStatus === 'online' && $tr069Status === 'online' && $serviceStatus === 'online') {
            $diagnosticStatus = 'normal';
        } elseif ($opticalStatus === 'online' && $tr069Status === 'stale' && $serviceStatus === 'online') {
            $diagnosticStatus = 'management_degraded';
        } elseif (in_array($opticalStatus, ['offline', 'los'])) {
            $diagnosticStatus = 'fiber_loss';
            if ($opticalStatus === 'offline' && $onu && $onu->rx_power_dbm === null) {
                $diagnosticStatus = 'onu_power_off'; // Just a heuristic
            }
        } elseif ($opticalStatus === 'online' && $tr069Status === 'offline' && $serviceStatus === 'online') {
            $diagnosticStatus = 'acs_unreachable';
        } elseif ($opticalStatus === 'online' && in_array($tr069Status, ['online', 'stale']) && $serviceStatus === 'offline') {
            $diagnosticStatus = 'pppoe_down';
        } elseif ($opticalStatus === 'online' && in_array($tr069Status, ['offline', 'unknown']) && $serviceStatus === 'offline') {
            $diagnosticStatus = 'service_down';
        } elseif ($serviceStatus === 'isolated') {
            $diagnosticStatus = 'isolated';
        }

        // Update DB if changed
        $hasChanged = (
            $service->optical_status !== $opticalStatus ||
            $service->tr069_status !== $tr069Status ||
            $service->service_status !== $serviceStatus ||
            $service->diagnostic_status !== $diagnosticStatus
        );

        if ($hasChanged) {
            $service->update([
                'optical_status' => $opticalStatus,
                'tr069_status' => $tr069Status,
                'service_status' => $serviceStatus,
                'diagnostic_status' => $diagnosticStatus,
                'last_state_change_at' => now(),
                'last_seen_at' => ($opticalStatus === 'online' || $serviceStatus === 'online') ? now() : $service->last_seen_at,
            ]);
            Log::info("CustomerService {$service->id} state updated to: {$diagnosticStatus}");
            
            event(new \App\Events\ISP\DeviceDiagnosisChanged($service->id, $diagnosticStatus, [
                'optical' => $opticalStatus,
                'tr069' => $tr069Status,
                'service' => $serviceStatus
            ]));
        } else {
            // Just update last_seen_at if it's online
            if ($opticalStatus === 'online' || $serviceStatus === 'online') {
                $service->update(['last_seen_at' => now()]);
            }
        }
    }
}
