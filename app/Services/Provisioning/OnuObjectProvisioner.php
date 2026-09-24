<?php
namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Support\Facades\Log;

class OnuObjectProvisioner
{
    private GenieACSDriver $acsDriver;

    public function __construct(GenieACSDriver $acsDriver)
    {
        $this->acsDriver = $acsDriver;
    }

    public function ensureObjects(Onu $onu, array &$currentParams, array $desiredState): bool
    {
        $deviceId = $currentParams['_id'] ?? $onu->genieacs_device_id;
        $modifiedAny = false;

        if (isset($desiredState['wan']) && is_array($desiredState['wan'])) {
            foreach ($desiredState['wan'] as $wanIndex => $wanConfig) {
                if (($wanConfig['mode'] ?? '') === 'pppoe') {
                    $wanDevicePath = "InternetGatewayDevice.WANDevice.1";
                    if (!$this->pathExists($currentParams, $wanDevicePath)) {
                        Log::warning("ONU_OBJECT_DISCOVERY: WANDevice.1 does not exist for ONU {$onu->id}");
                        continue;
                    }

                    // For ZTE, WANConnectionDevice instances aren't necessarily strictly equal to $wanIndex.
                    // But typically, we can just use the first available or ensure at least one exists.
                    // Let's check if ANY WANConnectionDevice exists, if not, create one.
                    $connInstances = $this->extractInstances($currentParams, "{$wanDevicePath}.WANConnectionDevice");
                    if (empty($connInstances)) {
                        Log::info("ONU_OBJECT_CREATE_REQUESTED: {$wanDevicePath}.WANConnectionDevice for ONU {$onu->id}");
                        $this->acsDriver->addObject($deviceId, "{$wanDevicePath}.WANConnectionDevice");
                        $this->waitForObjectAndRefresh($deviceId, $currentParams);
                        $modifiedAny = true;
                        $connInstances = $this->extractInstances($currentParams, "{$wanDevicePath}.WANConnectionDevice");
                    }

                    if (empty($connInstances)) {
                        Log::error("OBJECT_CREATION_FAILED: Could not create WANConnectionDevice");
                        continue;
                    }

                    // Use the first WANConnectionDevice
                    $connDeviceIdx = $connInstances[0];
                    $connectionDevicePath = "{$wanDevicePath}.WANConnectionDevice.{$connDeviceIdx}";

                    $pppConnectionPath = null;
                    if ($this->pathExists($currentParams, "{$connectionDevicePath}.WANPPPConnection")) {
                        $pppInstances = $this->extractInstances($currentParams, "{$connectionDevicePath}.WANPPPConnection");
                        if (!empty($pppInstances)) {
                            $pppConnectionPath = "{$connectionDevicePath}.WANPPPConnection." . $pppInstances[0];
                            Log::info("ONU_OBJECT_EXISTS: Using $pppConnectionPath for ONU {$onu->id}");
                        }
                    }

                    if (!$pppConnectionPath) {
                        Log::info("ONU_OBJECT_CREATE_REQUESTED: {$connectionDevicePath}.WANPPPConnection for ONU {$onu->id}");
                        $this->acsDriver->addObject($deviceId, "{$connectionDevicePath}.WANPPPConnection");
                        $this->waitForObjectAndRefresh($deviceId, $currentParams);
                        $modifiedAny = true;
                    }
                }
            }
        }

        return $modifiedAny;
    }

    private function waitForObjectAndRefresh(string $deviceId, array &$currentParams)
    {
        sleep(5); // Wait for GenieACS task to complete
        $currentParams = $this->acsDriver->getDeviceParameters($deviceId);
    }

    private function pathExists(array $params, string $path): bool
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) {
                return false;
            }
            $node = $node[$p];
        }
        return true;
    }

    private function extractInstances(array $params, string $path): array
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) {
                return [];
            }
            $node = $node[$p];
        }
        
        $instances = [];
        if (is_array($node)) {
            foreach (array_keys($node) as $key) {
                if (is_numeric($key)) {
                    $instances[] = $key;
                }
            }
        }
        return $instances;
    }
}
