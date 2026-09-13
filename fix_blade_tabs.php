<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Find the monitoring tab array element and remove it
$content = preg_replace('/\[\'tab\'\s*=>\s*\'monitoring\'.*?\],?\s*/', '', $content);
// Change device tab label to "Device & Monitor"
$content = preg_replace('/(\[\'tab\'\s*=>\s*\'device\',\s*\'label\'\s*=>\s*\')[^\']*(\',\s*\'icon\'\s*=>\s*\'router\'\])/', '${1}Device & Monitor$2', $content);

file_put_contents($file, $content);
echo "Fixed tabs in blade view.";
?>
