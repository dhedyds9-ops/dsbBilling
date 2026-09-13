<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Replace Google Satellite URL
$search = "https://mt{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}";
$replace = "https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed Google Satellite URL.";
?>
