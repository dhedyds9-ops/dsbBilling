<?php
$file = 'app/Livewire/ACS/Device/Index.php';
$content = file_get_contents($file);

$search1 = <<<'PHP'
            $query = [
                'projection' => '_id,_deviceId,_lastInform,VirtualParameters,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL'
            ];
PHP;

$replace1 = <<<'PHP'
            $query = [
                'projection' => '_id,_deviceId,_lastInform,VirtualParameters,InternetGatewayDevice.DeviceInfo,Device.DeviceInfo,InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection,InternetGatewayDevice.ManagementServer.ConnectionRequestURL,Device.ManagementServer.ConnectionRequestURL,InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower,InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower,InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower,InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower,InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower,InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower,Device.Optical.1.Transceiver.RxPower'
            ];
PHP;

$content = str_replace($search1, $replace1, $content);

$search2 = <<<'PHP'
                $pppoeUsername = $deviceData['VirtualParameters']['pppoeUsername']['_value'] ?? 
                                 $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? 
                                 $deviceData['Device']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? null;

                $acsDeviceParams = [
PHP;

$replace2 = <<<'PHP'
                $pppoeUsername = $deviceData['VirtualParameters']['pppoeUsername']['_value'] ?? 
                                 $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? 
                                 $deviceData['Device']['WANDevice'][1]['WANConnectionDevice'][1]['WANPPPConnection'][1]['Username']['_value'] ?? null;

                $rxPower = $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_FH_GponInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_ZTE-COM_WANPONInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['X_HW_PONInterfaceConfig']['RXPower']['_value']
                        ?? $deviceData['VirtualParameters']['RXPower']['_value']
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANPONInterfaceConfig'][1]['X_ZTE-COM_RxPower']['_value'] 
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANPONInterfaceConfig'][1]['X_HW_RxPower']['_value'] 
                        ?? $deviceData['InternetGatewayDevice']['WANDevice'][1]['WANEponInterfaceConfig'][1]['RxPower']['_value']
                        ?? $deviceData['Device']['Optical'][1]['Transceiver']['RxPower']['_value']
                        ?? null;
                
                if ($rxPower !== null && is_numeric($rxPower)) {
                    $rxPower = (float)$rxPower;
                    if ($rxPower < -500 || $rxPower > 500) {
                        $rxPower /= 1000;
                    } elseif ($rxPower < -50 || $rxPower > 50) {
                        $rxPower /= 100;
                    }
                    $rxPower = round($rxPower); // Since migration is integer, we round it
                }

                $acsDeviceParams = [
                    'signal' => $rxPower,
PHP;

$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
echo "Index.php optical power extracted!\n";
