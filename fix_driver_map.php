<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = <<<'PHP'
    public function updateWifiPassword(string $deviceId, string $newPassword, string $vendor = 'default'): bool
    {
        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'wpa_passphrase') => $newPassword,
            $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key') => $newPassword,
            $this->getWifiParameterPath($vendor, 'security_mode') => 'WPA2PSK',
        ]);
    }
PHP;
$search = str_replace("\r\n", "\n", $search);

$replace = <<<'PHP'
    public function updateWifiPassword(string $deviceId, string $newPassword, string $vendor = 'default'): bool
    {
        // Try to guess correct security value based on path
        $secPath = $this->getWifiParameterPath($vendor, 'security_mode');
        $secValue = str_contains($secPath, 'BeaconType') ? '11i' : 'WPA2-Personal';

        return $this->setParameterValues($deviceId, [
            $this->getWifiParameterPath($vendor, 'wpa_passphrase') => $newPassword,
            $this->getWifiParameterPath($vendor, 'wpa_pre_shared_key') => $newPassword,
            $secPath => $secValue,
        ]);
    }
PHP;
$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated with mapping.\n";
