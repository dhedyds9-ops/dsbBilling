<?php
$log = file_get_contents('storage/logs/laravel.log');
// Get the last 200 lines
$lines = explode("\n", $log);
$lastLines = array_slice($lines, -150);
echo implode("\n", $lastLines);
