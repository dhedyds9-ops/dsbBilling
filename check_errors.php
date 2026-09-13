<?php
$logPath = 'D:/dsBilling/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $errors = array_filter($lines, function($line) {
        return strpos($line, 'local.ERROR') !== false;
    });
    echo implode("", array_slice($errors, -10));
}
?>
