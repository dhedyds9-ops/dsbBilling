<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/hotspot.blade.php';
$content = file_get_contents($file);

$content = str_replace("route('reseller-portal.customers.create')", "route('reseller-portal.customers.hotspot.create')", $content);

file_put_contents($file, $content);
echo "Updated hotspot.blade.php\n";
?>
