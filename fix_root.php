<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$rootDiv = '<div class="space-y-6 pb-10">';
$goodBlock = <<<HTML
<div class="space-y-6 pb-10">
    {{-- Leaflet Dependencies --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
HTML;

$content = preg_replace('/<div class="space-y-6 pb-10">/', $goodBlock, $content, 1);
file_put_contents($file, $content);
echo "Added Leaflet CSS and JS to root div.";
?>
