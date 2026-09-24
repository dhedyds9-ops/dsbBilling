<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search1 = <<<'PHP'
            $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
PHP;

$replace1 = <<<'PHP'
            $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? $params['Device']['WANDevice'] ?? [];
PHP;

$content = str_replace($search1, $replace1, $content);

file_put_contents($file, $content);
echo "Show.php TR-181 WANDevice added!\n";
