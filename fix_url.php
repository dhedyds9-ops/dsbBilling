<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = '/tasks?timeout=1500&connection_request';
$replace = '/tasks?connection_request';

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated: removed timeout from URL.\n";
