<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = '->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);';
$replace = '->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?timeout=3000&connection_request", $payload);';

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated.\n";
