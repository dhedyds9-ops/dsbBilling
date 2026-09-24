<?php
$file = 'app/Console/Commands/AcsDiscoverPaths.php';
$content = file_get_contents($file);
$search = "\$device = ACSDevice::where('serial_number', \$sn)->first();";
$replace = "\$device = ACSDevice::where('serial_number', 'LIKE', '%' . trim(\$sn) . '%')->first();";
$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "acs:discover fixed!\n";
