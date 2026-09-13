<?php
$src = 'D:/dsBilling/resources/views/livewire/isp/pppoe-user/create.blade.php';
$dst = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php';
$content = file_get_contents($src);

// Remove Reseller ID dropdown
$content = preg_replace('/<div[^>]*>.*?wire:model="reseller_id".*?<\/div>.*?<\/div>/s', '', $content);

// Replace wire:model="username" with pppoe_username
$content = str_replace('wire:model.live="username"', 'wire:model.live="pppoe_username"', $content);
$content = str_replace('wire:model="password"', 'wire:model="pppoe_password"', $content);
$content = str_replace("@error('username')", "@error('pppoe_username')", $content);
$content = str_replace("@error('password')", "@error('pppoe_password')", $content);

// Route mapping for Batal button
$content = str_replace("route('isp.pppoe-users.index')", "route('reseller-portal.customers.index')", $content);

file_put_contents($dst, $content);
echo "Patched create blade with PPPoE form.\n";
?>
