<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php';
$content = file_get_contents($file);

$content = str_replace("     @section('header_title', 'Speedtest') \n", "", $content);
$search = '     x-data="{';
$replace = $search . "\n        // ...\n     \">\n    @section('header_title', 'Speedtest')\n";
// Wait, better to use regex to fix it cleanly.

$content = preg_replace('/<div class="([^"]+)"\s*@section\(\'header_title\', \'Speedtest\'\)\s*x-data="/sm', '<div class="$1" x-data="', $content);

$content = preg_replace('/(x-data="\{[^}]+\}")\s*>/sm', "$1>\n    @section('header_title', 'Speedtest')", $content);

file_put_contents($file, $content);
echo "Fixed @section position.\n";
?>
