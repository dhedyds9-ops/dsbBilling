<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Support\Carbon;

class SyncDeviceStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(GenieACSDriver $driver): void
    {
        $devices = ACSDevice::all();
        
        foreach ($devices as $device) {
            try {
                $acsId = $device->uuid ?: $device->serial_number;
                $params = $driver->getDeviceParameters($acsId);
                
                $lastInform = $params['_lastInform'] ?? null;
                $online = $lastInform && abs(now()->diffInMinutes(Carbon::parse($lastInform))) < 5;

                $device->update([
                    'status' => $online ? 'online' : 'offline',
                    'last_inform' => $lastInform,
                    'last_contact' => now(),
                ]);

                if ($device->onu) {
                    $device->onu->update([
                        'last_seen_at' => $lastInform,
                        'status' => $online ? 'active' : 'inactive',
                    ]);
                }
            } catch (\Exception $e) {
                // If device not found or ACS is unreachable, mark offline
                $device->update([
                    'status' => 'offline',
                ]);
            }
        }
    }
}
