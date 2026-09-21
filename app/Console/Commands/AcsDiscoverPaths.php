<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Models\ACS\ACSDevice;

class AcsDiscoverPaths extends Command
{
    protected $signature = 'acs:discover {sn}';
    protected $description = 'Discover TR-069 paths for a specific device (to help map vendor parameters)';

    public function handle()
    {
        $sn = $this->argument('sn');
        $device = ACSDevice::where('serial_number', 'LIKE', '%' . trim($sn) . '%')->first();
        if (!$device) {
            $this->error("Device with SN {$sn} not found in database.");
            return;
        }

        $this->info("Fetching data from GenieACS for SN: {$sn} (UUID: {$device->uuid})...");
        
        try {
            $driver = new GenieACSDriver();
            $params = $driver->getDeviceParameters($device->uuid);
        } catch (\Exception $e) {
            $this->error("Failed to fetch from GenieACS: " . $e->getMessage());
            return;
        }

        $this->info("Vendor: " . ($device->vendor ?? 'Unknown'));
        $this->info("Product Class: " . ($device->product_class ?? 'Unknown'));
        $this->line("---");
        
        $flatParams = [];
        $this->flattenParams($params, '', $flatParams);

        $keywords = ['WLAN', 'SSID', 'Optical', 'TransmitPower', 'ReceivePower', 'RXPower', 'TXPower', 'WANPPP', 'WANIP', 'NAT', 'VLAN', 'Bind', 'ServiceList'];
        
        $this->info("Found " . count($flatParams) . " total parameters. Filtering for interesting paths...");
        
        foreach ($flatParams as $path => $value) {
            foreach ($keywords as $kw) {
                if (stripos($path, $kw) !== false) {
                    // Only print if it's a leaf node (has _value) or if we just want the path
                    $valStr = is_scalar($value) ? $value : (isset($value['_value']) ? $value['_value'] : 'Array/Object');
                    $this->line("<fg=yellow>{$path}</> = <fg=cyan>{$valStr}</>");
                    break;
                }
            }
        }
        
        $this->info("Done! Copy the output above and send it to the developer.");
    }

    private function flattenParams($array, $prefix, &$result)
    {
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            
            if ($key === '_value') {
                $result[substr($prefix, 0, -7)] = $value; // remove ._value
                continue;
            }
            
            if (is_array($value) && !isset($value['_value'])) {
                $this->flattenParams($value, $newKey, $result);
            } elseif (is_array($value) && isset($value['_value'])) {
                $result[$newKey] = $value['_value'];
            } else {
                if ($key !== '_id' && $key !== '_lastUpdated') {
                    $result[$newKey] = $value;
                }
            }
        }
    }
}
