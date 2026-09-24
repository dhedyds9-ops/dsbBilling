<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

$search = "|| str_contains(\$lower, 'cannot find')";
$replace = "/* || str_contains(\$lower, 'cannot find') */";
$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Fixed isCliErrorMessage\n";
