<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ACS\ACSDevice;
use App\Events\ISP\Tr069StatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GenieAcsWebhookController extends Controller
{
    public function handleEvent(Request $request)
    {
        // Example Payload from GenieACS Provision Script:
        // { "event": "0 BOOT", "device": "000000-MODEL-12345", "timestamp": "2026-08-19T10:00:00Z" }
        
        $payload = $request->all();
        $eventString = $payload['event'] ?? '';
        $deviceId = $payload['device'] ?? '';
        
        if (!$deviceId) {
            return response()->json(['status' => 'error', 'message' => 'Missing device ID'], 400);
        }

        $device = ACSDevice::where('uuid', $deviceId)
            ->orWhere('serial_number', $deviceId)
            ->first();
            
        if (!$device) {
            // Auto-discover handled by monitor-alarms for now, or we can trigger it here
            return response()->json(['status' => 'ignored', 'message' => 'Device not found locally'], 200);
        }

        $oldStatus = $device->status;
        $newStatus = 'online';

        if (str_contains($eventString, 'OFFLINE')) { // Custom logic if provision script detects offline
            $newStatus = 'offline';
        }

        if ($oldStatus !== $newStatus || str_contains($eventString, 'BOOT') || str_contains($eventString, 'INFORM')) {
            $device->status = $newStatus;
            $device->last_contact = now();
            $device->last_inform = now();
            $device->save();
            
            event(new Tr069StatusChanged($deviceId, $oldStatus, $newStatus, now()));
        }

        return response()->json(['status' => 'success']);
    }
}
