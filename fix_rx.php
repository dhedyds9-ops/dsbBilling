<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search = <<<'PHP'
            // Common TR-069 Paths for PON
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.1.RxPower');
            
            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('VirtualParameters.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.1.TxPower');
PHP;

$replace = <<<'PHP'
            // Common TR-069 Paths for PON
            $rxPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower')
                    ?? $extract('VirtualParameters.RXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower') 
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.RxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.1.RxPower');
            
            $txPower = $extract('InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower')
                    ?? $extract('VirtualParameters.TXPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANEponInterfaceConfig.1.TxPower')
                    ?? $extract('InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.1.TxPower');
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.php RX/TX paths updated for Fiberhome.\n";
