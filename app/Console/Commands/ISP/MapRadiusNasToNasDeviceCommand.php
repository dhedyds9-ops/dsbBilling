<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\NasDevice;
use App\Models\ISP\RadiusNas;
use Illuminate\Console\Command;

class MapRadiusNasToNasDeviceCommand extends Command
{
    protected $signature = 'radius:nas-map-device';
    protected $description = 'Auto-map kolom radius_nas.nas_device_id berdasarkan kesamaan IP address dengan nas_devices';

    public function handle(): int
    {
        $updated = 0;
        $newNasCreated = 0;
        $missing = 0;

        RadiusNas::query()
            ->where(function ($q) {
                $q->whereNull('nas_device_id')
                    ->orWhereRaw('nas_device_id NOT IN (SELECT id FROM nas_devices)');
            })
            ->whereNotNull('nas_ip_address')
            ->chunkById(200, function ($nases) use (&$updated) {
                foreach ($nases as $nas) {
                    $device = NasDevice::active()
                        ->where('ip_address', $nas->nas_ip_address)
                        ->first(['id']);
                    if ($device) {
                        $nas->nas_device_id = $device->id;
                        $nas->timestamps = false;
                        $nas->save();
                        $updated++;
                    }
                }
            });

        NasDevice::active()
            ->whereNotIn('id', function ($q) {
                $q->select('nas_device_id')
                    ->from('radius_nas')
                    ->whereNotNull('nas_device_id');
            })
            ->select(['id', 'name', 'ip_address', 'nas_type'])
            ->chunkById(200, function ($devices) use (&$newNasCreated) {
                foreach ($devices as $device) {
                    $secret = bin2hex(random_bytes(16));
                    RadiusNas::create([
                        'uuid' => (string)\Illuminate\Support\Str::uuid(),
                        'nas_name' => $device->name,
                        'nas_ip_address' => $device->ip_address,
                        'nas_secret' => $secret,
                        'nas_device_id' => $device->id,
                        'nas_type' => $device->nas_type ?? 'other',
                        'status' => 'active',
                    ]);
                    $newNasCreated++;
                }
            });

        $missing = RadiusNas::query()->whereNull('nas_device_id')->count();

        $this->info("NasDevice ↔ RadiusNas unification:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Existing RadiusNas updated nas_device_id', $updated],
                ['New RadiusNas peers created from NasDevice', $newNasCreated],
                ['RadiusNas rows still missing nas_device_id', $missing],
            ]
        );

        return self::SUCCESS;
    }
}
