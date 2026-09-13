<?php
$src = 'D:/dsBilling/resources/views/livewire/isp/pppoe-user/create.blade.php';
$dst = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php';
$content = file_get_contents($src);

// Replace route('isp.pppoe-users.index') with route('reseller-portal.customers.index')
$content = str_replace("route('isp.pppoe-users.index')", "route('reseller-portal.customers.index')", $content);

// Remove reseller dropdown
$content = preg_replace('/<div[^>]*>.*?wire:model="reseller_id".*?<\/div>.*?<\/div>/s', '', $content);
// This regex might fail to cleanly remove the div. Let's just remove the 'Kepemilikan' block.
$content = preg_replace('/<!-- Section 4: Afiliasi.*?-->.*?<div class="grid grid-cols-1 md:grid-cols-2 gap-6">.*?<\/div>.*?<\/div>/s', '', $content);

file_put_contents($dst, $content);
echo "Restored create.blade.php\n";
?>
