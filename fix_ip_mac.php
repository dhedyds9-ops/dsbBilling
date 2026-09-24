<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search1 = <<<'PHP'
            // SSIDs
            $ssid1 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID') ?? $extract('Device.WiFi.SSID.1.SSID');
            $ssid2 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID') ?? $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID') ?? $extract('Device.WiFi.SSID.2.SSID');

            $this->deviceStatus = [
                'rx_power' => $rxPower !== null ? round($rxPower, 2) . ' dBm' : '-',
                'tx_power' => $txPower !== null ? round($txPower, 2) . ' dBm' : '-',
                'pppoe_username' => $pppoeUser ?: '-',
                'pppoe_password' => $pppoePass ?: '-',
                'ssid_1' => $ssid1 ?: '-',
                'ssid_2' => $ssid2 ?: '-',
            ];
PHP;

$replace1 = <<<'PHP'
            // SSIDs
            $ssid1 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID') ?? $extract('Device.WiFi.SSID.1.SSID');
            $ssid2 = $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID') ?? $extract('InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID') ?? $extract('Device.WiFi.SSID.2.SSID');

            // Find WAN IP & MAC dynamically by scanning TR-098 WANDevice
            $wanIp = null;
            $wanMac = null;
            $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
            if (is_array($wanDevices)) {
                foreach ($wanDevices as $wdIndex => $wdNode) {
                    if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;
                    $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                    if (!is_array($connDevices)) continue;
                    foreach ($connDevices as $cdIndex => $cdNode) {
                        if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;
                        
                        // Check PPPoE
                        $ppp = $cdNode['WANPPPConnection'] ?? [];
                        if (is_array($ppp)) {
                            foreach ($ppp as $pIndex => $pNode) {
                                if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                                if (isset($pNode['ExternalIPAddress']['_value']) && $pNode['ExternalIPAddress']['_value'] && $pNode['ExternalIPAddress']['_value'] !== '0.0.0.0') {
                                    $wanIp = $pNode['ExternalIPAddress']['_value'];
                                }
                                if (isset($pNode['MACAddress']['_value']) && $pNode['MACAddress']['_value']) {
                                    $wanMac = $pNode['MACAddress']['_value'];
                                }
                            }
                        }
                        
                        // Check IPoE
                        $ip = $cdNode['WANIPConnection'] ?? [];
                        if (is_array($ip)) {
                            foreach ($ip as $iIndex => $iNode) {
                                if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                                if (isset($iNode['ExternalIPAddress']['_value']) && $iNode['ExternalIPAddress']['_value'] && $iNode['ExternalIPAddress']['_value'] !== '0.0.0.0') {
                                    $wanIp = $iNode['ExternalIPAddress']['_value'];
                                }
                                if (isset($iNode['MACAddress']['_value']) && $iNode['MACAddress']['_value']) {
                                    $wanMac = $iNode['MACAddress']['_value'];
                                }
                            }
                        }
                    }
                }
            }

            $this->deviceStatus = [
                'rx_power' => $rxPower !== null ? round($rxPower, 2) . ' dBm' : '-',
                'tx_power' => $txPower !== null ? round($txPower, 2) . ' dBm' : '-',
                'pppoe_username' => $pppoeUser ?: '-',
                'pppoe_password' => $pppoePass ?: '-',
                'ssid_1' => $ssid1 ?: '-',
                'ssid_2' => $ssid2 ?: '-',
                'wan_ip' => $wanIp ?: '-',
                'wan_mac' => $wanMac ?: '-',
            ];
PHP;

$content = str_replace($search1, $replace1, $content);

file_put_contents($file, $content);
echo "Show.php ip and mac updated!\n";
