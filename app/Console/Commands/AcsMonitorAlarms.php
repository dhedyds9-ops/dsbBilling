<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\ACSAlarm;
use App\Models\ACS\ACSLog;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AcsMonitorAlarms extends Command
{
    protected $signature = 'acs:monitor-alarms';
    protected $description = 'Monitor ACS devices for alarms (Offline, bad RX Power) and notify via Telegram';

    public function handle(TelegramService $telegram)
    {
        $this->info('Starting ACS alarm monitoring...');
        
        $acs = new GenieACSDriver();
        
        try {
            // Fetch devices from GenieACS in bulk to avoid hammering the API
            $acsDevices = $acs->listDevices([], 5000);
        } catch (\Exception $e) {
            $this->error('Failed to connect to GenieACS: ' . $e->getMessage());
            return 1;
        }

        $localDevices = ACSDevice::all()->keyBy('uuid');

        foreach ($acsDevices as $deviceData) {
            $deviceId = $deviceData['_id'];
            if (!$localDevices->has($deviceId)) {
                // Auto-discover new ACS Device
                $sn = $deviceData['_deviceId']['_SerialNumber'] ?? null;
                $mac = null; // Can extract from WANDevice or let the system update later
                if (isset($deviceData['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['1']['WANIPConnection']['1']['MACAddress']['_value'])) {
                    $mac = $deviceData['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['1']['WANIPConnection']['1']['MACAddress']['_value'];
                }
                
                $device = ACSDevice::create([
                    'uuid' => $deviceId,
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'manufacturer' => $deviceData['_deviceId']['_Manufacturer'] ?? null,
                    'product_class' => $deviceData['_deviceId']['_ProductClass'] ?? null,
                    'status' => 'online', // Initial assumption if we just got it
                ]);
                $localDevices->put($deviceId, $device);
                
                try {
                    app(\App\Services\ISP\CorrelationService::class)->evaluateAcsDevice($device);
                } catch (\Throwable $e) {
                    Log::error('CorrelationService evaluateAcsDevice error', ['msg' => $e->getMessage()]);
                }
            }

            $device = $localDevices->get($deviceId);
            $lastInform = $deviceData['_lastInform'] ?? null;
            
            // Extract PeriodicInformInterval
            $informInterval = 300; // Default 5 mins
            if (isset($deviceData['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'])) {
                $informInterval = (int)$deviceData['InternetGatewayDevice']['ManagementServer']['PeriodicInformInterval']['_value'];
            } elseif (isset($deviceData['Device']['ManagementServer']['PeriodicInformInterval']['_value'])) {
                $informInterval = (int)$deviceData['Device']['ManagementServer']['PeriodicInformInterval']['_value'];
            }

            $newStatus = 'offline';
            if ($lastInform) {
                $diffInSeconds = now()->diffInSeconds(Carbon::parse($lastInform));
                $offlineThreshold = ($informInterval * 2) + 120; // 2x interval + 2 mins grace period
                
                if ($diffInSeconds <= $informInterval + 60) {
                    $newStatus = 'online';
                } elseif ($diffInSeconds <= $offlineThreshold) {
                    $newStatus = 'stale';
                }
            }

            // Extract RX Power from bulk device data
            $rxPower = null;
            $rxPaths = [
                'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower._value',
                'Device.Optical.1.Transceiver.RxPower._value',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_RxPower._value'
            ];

            foreach ($rxPaths as $path) {
                $parts = explode('.', $path);
                $node = $deviceData;
                $found = true;
                foreach ($parts as $p) {
                    if (!isset($node[$p])) {
                        $found = false;
                        break;
                    }
                    $node = $node[$p];
                }
                if ($found && $node !== '') {
                    // Convert to dBm if needed
                    $val = is_numeric($node) ? (float)$node / (($node > 1000 || $node < -1000) ? 1000 : 1) : null;
                    if ($val !== null && $val != 0) {
                        $rxPower = $val;
                        break;
                    }
                }
            }

            $deviceChanged = false;

            // Check Offline Alarm (with Mass Outage Suppression)
            if (in_array($device->status, ['online', 'stale']) && $newStatus === 'offline') {
                $this->triggerAlarm($device, 'critical', 'Perangkat Offline', "Modem tidak menghubungi server ACS melampaui batas toleransi (Inform Interval).", $telegram, suppressIfIncident: true);
                $deviceChanged = true;
            } elseif ($device->status === 'offline' && in_array($newStatus, ['online', 'stale'])) {
                $this->triggerLog($device, 'info', 'Perangkat Online Kembali', 'Modem kembali terhubung ke server ACS.');
                $telegram->sendAlarmNotification($device->serial_number, 'ONLINE', 'Perangkat kembali terhubung.');
                
                // Automatically acknowledge existing offline alarms
                ACSAlarm::where('acs_device_id', $device->id)
                    ->where('status', 'active')
                    ->where('message', 'Perangkat Offline')
                    ->update(['status' => 'resolved', 'acknowledged_at' => now(), 'updated_at' => now()]);
                
                $deviceChanged = true;
            }

            // Check RX Power Alarm (Only if online to avoid false alarms)
            if ($newStatus === 'online' && $rxPower !== null) {
                if ($rxPower < -27) {
                    // Check if we already have an active alarm for this
                    $existingAlarm = clone ACSAlarm::where('acs_device_id', $device->id)
                        ->where('status', 'active')
                        ->where('message', 'LIKE', '%Redaman Buruk%')
                        ->first();
                        
                    if (!$existingAlarm) {
                        $this->triggerAlarm($device, 'warning', 'Redaman Buruk (LOS Risk)', "Sinyal RX Power terdeteksi: {$rxPower} dBm", $telegram);
                    }
                } else {
                    // Resolve bad rx power alarms if rx is good again
                    ACSAlarm::where('acs_device_id', $device->id)
                        ->where('status', 'active')
                        ->where('message', 'LIKE', '%Redaman Buruk%')
                        ->update(['status' => 'resolved', 'acknowledged_at' => now(), 'updated_at' => now()]);
                }
            }

            // Update local DB
            if ($deviceChanged || $device->status !== $newStatus) {
                $oldStatus = $device->status;
                $device->status = $newStatus;
                $device->last_contact = now();
                if ($lastInform) $device->last_inform = Carbon::parse($lastInform);
                $device->save();
                
                if ($oldStatus !== $newStatus) {
                    $ident = $device->serial_number ?: $device->mac_address ?: $device->uuid;
                    event(new \App\Events\ISP\Tr069StatusChanged($ident, $oldStatus, $newStatus));
                }
            }
        }
        
        $this->info('Monitoring completed.');
        return 0;
    }

    protected function triggerAlarm(ACSDevice $device, string $severity, string $message, string $details, TelegramService $telegram, bool $suppressIfIncident = false)
    {
        ACSAlarm::create([
            'uuid' => (string) Str::uuid(),
            'acs_device_id' => $device->id,
            'severity' => $severity,
            'message' => $message,
            'details' => ['description' => $details],
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Suppress individual Telegram notifications if a Mass Outage incident is active
        if ($suppressIfIncident) {
            $hasActiveIncident = \App\Models\ISP\NetworkIncident::where('status', 'active')->exists();
            if ($hasActiveIncident) {
                return; // Alarm recorded in DB, but Telegram is suppressed
            }
        }
        
        $statusText = $severity === 'critical' ? 'OFFLINE' : 'WARNING';
        $telegram->sendAlarmNotification($device->serial_number, $statusText, $message . ' - ' . $details);
    }
    
    protected function triggerLog(ACSDevice $device, string $type, string $message, string $details)
    {
        ACSLog::create([
            'uuid' => (string) Str::uuid(),
            'acs_device_id' => $device->id,
            'type' => $type,
            'message' => $message,
            'details' => ['description' => $details],
        ]);
    }
}
