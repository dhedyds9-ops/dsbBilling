<?php
$file = 'app/Livewire/ACS/Device/Index.php';
$content = file_get_contents($file);

$search = <<<'PHP'
                $firmware = $extract('InternetGatewayDevice.DeviceInfo.SoftwareVersion') ?? $extract('Device.DeviceInfo.SoftwareVersion');
                $hardware = $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion') ?? $extract('Device.DeviceInfo.HardwareVersion');
                
                $pppoeUsername = $extract('VirtualParameters.pppoeUsername') ?? 
                                 $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username') ?? 
                                 $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');

                $extract = function($path) use ($deviceData) {
                    $parts = explode('.', $path);
                    $node = $deviceData;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) return null;
                        $node = $node[$p];
                    }
                    return isset($node['_value']) ? $node['_value'] : null;
                };
PHP;

$replace = <<<'PHP'
                $extract = function($path) use ($deviceData) {
                    $parts = explode('.', $path);
                    $node = $deviceData;
                    foreach ($parts as $p) {
                        if (!is_array($node) || !array_key_exists($p, $node)) return null;
                        $node = $node[$p];
                    }
                    return isset($node['_value']) ? $node['_value'] : null;
                };

                $firmware = $extract('InternetGatewayDevice.DeviceInfo.SoftwareVersion') ?? $extract('Device.DeviceInfo.SoftwareVersion');
                $hardware = $extract('InternetGatewayDevice.DeviceInfo.HardwareVersion') ?? $extract('Device.DeviceInfo.HardwareVersion');
                
                $pppoeUsername = $extract('VirtualParameters.pppoeUsername') ?? 
                                 $extract('InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username') ?? 
                                 $extract('Device.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username');
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed extract order!\n";
