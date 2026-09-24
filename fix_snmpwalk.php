<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

$content = str_replace(
    'snmpwalk -O q -v %s',
    'snmpwalk -O n -v %s',
    $content
);

file_put_contents($file, $content);
echo "Replaced -O q with -O n in SnmpClient.php\n";
