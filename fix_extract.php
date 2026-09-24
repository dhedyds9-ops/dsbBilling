<?php
$file = 'app/Livewire/ACS/Device/Index.php';
$content = file_get_contents($file);

$search1 = <<<'PHP'
                $rxPower = $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_FH_GponInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_ZTE-COM_WANPONInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_HW_PONInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['VirtualParameters']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANPONInterfaceConfig'][1]['X_ZTE-COM_RxPower']['_value'] 
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANPONInterfaceConfig'][1]['X_HW_RxPower']['_value'] 
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANEponInterfaceConfig'][1]['RxPower']['_value']
                        ?? $deviceData['Device']['Optical'][1]['Transceiver']['RxPower']['_value']
                        ?? null;
PHP;

$replace1 = <<<'PHP'
                $extract = function($path) use ($deviceData) {
                    $parts = explode('.', $path);
                    $node = $deviceData;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) return null;
                        $node = $node[$p];
                    }
                    return isset($node['_value']) ? $node['_value'] : null;
                };

                $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower')
                        ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                        ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower')
                        ?? $extract('VirtualParameters.RXPower')
                        ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                        ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower') 
                        ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                        ?? $extract('Device.Optical.1.Transceiver.RxPower');
PHP;

$content = str_replace($search1, $replace1, $content);

$search2 = <<<'PHP'
                $firmware = $deviceData['InternetGatewayDevice']['DeviceInfo']['SoftwareVersion']['_value'] ??
                            $deviceData['Device']['DeviceInfo']['SoftwareVersion']['_value'] ?? null;
                            
                $hardware = $deviceData['InternetGatewayDevice']['DeviceInfo']['HardwareVersion']['_value'] ??
                            $deviceData['Device']['DeviceInfo']['HardwareVersion']['_value'] ?? null;

                $pppoeUsername = $deviceData['VirtualParameters']['pppoeUsername']['_value'] ?? 
                                 $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? 
                                 $deviceData['Device']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? null;
PHP;

$replace2 = <<<'PHP'
                $firmware = $extract('InternetGatewayDevice.DeviceInfo.SoftwareVersion') ?? $extract('Device.DeviceInfo.SoftwareVersion');
                $hardware = $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion') ?? $extract('Device.DeviceInfo.HardwareVersion');
                
                $pppoeUsername = $extract('VirtualParameters.pppoeUsername') ?? 
                                 $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username') ?? 
                                 $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');
PHP;

$content = str_replace($search2, $replace2, $content);

$search3 = <<<'PHP'
                    'firmware_version' => $firmware,
                    'hardware_version' => $hardware,
PHP;

$replace3 = <<<'PHP'
                    'firmware_version' => $firmware,
                    'software_version' => $firmware,
                    'hardware_version' => $hardware,
PHP;

$content = str_replace($search3, $replace3, $content);

file_put_contents($file, $content);
echo "Index.php safely refactored!\n";
