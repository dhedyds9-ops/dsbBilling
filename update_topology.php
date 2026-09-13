<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

// Find the devices extraction logic
$search = <<<PHP
                if (\$service->onu) {
                    \$devices[] = [
                        'id' => \$service->onu->id, 
                        'type' => 'ONT', 
                        'brand' => \$service->onu->brand ?? 'Unknown', 
                        'model' => \$service->onu->model ?? 'Unknown', 
                        'serial' => \$service->onu->serial_number ?? 'Unknown', 
                        'status' => \$service->onu->status ?? 'active',
                        'wifi_ssid' => \$service->onu->wifi_ssid ?? '-',
                        'wifi_password' => \$service->onu->wifi_password ?? '-',
                    ];
                }
PHP;

$replace = <<<PHP
                if (\$service->onu) {
                    \$devices[] = [
                        'id' => \$service->onu->id, 
                        'type' => 'ONT', 
                        'brand' => \$service->onu->brand ?? 'Unknown', 
                        'model' => \$service->onu->model ?? 'Unknown', 
                        'serial' => \$service->onu->serial_number ?? 'Unknown', 
                        'status' => \$service->onu->status ?? 'active',
                        'wifi_ssid' => \$service->onu->wifi_ssid ?? '-',
                        'wifi_password' => \$service->onu->wifi_password ?? '-',
                        'topology' => [
                            'odp' => \$service->onu->odp->name ?? 'Belum terhubung ODP',
                            'odc' => \$service->onu->odp->odc->name ?? '-',
                            'pon_port' => \$service->onu->formatted_pon_port ?? \$service->onu->ponPort->name ?? '-',
                            'olt' => \$service->onu->olt->name ?? 'Belum terhubung OLT',
                            'onu_id' => \$service->onu->onu_id_on_olt ?? '-'
                        ]
                    ];
                }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated devices array with topology info in Customer360.php.";
?>
