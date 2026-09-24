<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

$content = str_replace(
    '2>&1',
    '2>/dev/null',
    $content
);

file_put_contents($file, $content);
echo "Replaced 2>&1 with 2>/dev/null in SnmpClient.php\n";
