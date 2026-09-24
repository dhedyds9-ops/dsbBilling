<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

$content = str_replace("2>/dev/null", "' . (PHP_OS_FAMILY === 'Windows' ? '2>nul' : '2>/dev/null')", $content);
// Oh wait, inside sprintf that won't work well if I just replace it blindly.
// Let's do it right.
