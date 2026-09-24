<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search1 = <<<'PHP'
            // Common TR-069 Paths for PON
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                    ?? $extract('Device.Optical.1.Transceiver.RxPower');
            if ($rxPower !== null && is_numeric($rxPower)) {
                $rxPower = (float)$rxPower;
                if ($rxPower > 1000 || $rxPower < -1000) $rxPower /= 1000;
            }

            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower')
                    ?? $extract('Device.Optical.1.Transceiver.TxPower');
            if ($txPower !== null && is_numeric($txPower)) {
                $txPower = (float)$txPower;
                if ($txPower > 1000 || $txPower < -1000) $txPower /= 1000;
            }
PHP;

$replace1 = <<<'PHP'
            // Common TR-069 Paths for PON
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                    ?? $extract('Device.Optical.1.Transceiver.RxPower');
                    
            if ($rxPower !== null && is_numeric($rxPower)) {
                $rxPower = (float)$rxPower;
                // Fiberhome reports as -19.03, ZTE as -22.01 (already in dBm)
                // If it's something like -22010, then divide by 1000
                if ($rxPower < -500 || $rxPower > 500) {
                    $rxPower /= 1000;
                } elseif ($rxPower < -50 || $rxPower > 50) {
                    $rxPower /= 100;
                }
            }

            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower')
                    ?? $extract('VirtualParameters.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower')
                    ?? $extract('Device.Optical.1.Transceiver.TxPower');
                    
            if ($txPower !== null && is_numeric($txPower)) {
                $txPower = (float)$txPower;
                if ($txPower < -500 || $txPower > 500) {
                    $txPower /= 1000;
                } elseif ($txPower < -50 || $txPower > 50) {
                    $txPower /= 100;
                }
            }
PHP;
$content = str_replace($search1, $replace1, $content);

file_put_contents($file, $content);
echo "Show.php optical power updated!\n";
