<?php
$src = 'D:/dsBilling/resources/views/livewire/isp/voucher/index.blade.php';
$dst = 'D:/dsBilling/resources/views/livewire/reseller-portal/sales/voucher.blade.php';

$content = file_get_contents($src);

// Replace "isp.voucher" routes with "reseller-portal.sales.voucher"
$content = preg_replace('/route\(\'isp\.vouchers\.([^\']+)\'\)/', "route('reseller-portal.sales.voucher')", $content);

// Remove the Reseller select element from filter and generate modals
// Filter modal:
$content = preg_replace('/<div[^>]*>.*?<label[^>]*>Reseller<\/label>.*?wire:model="filters\.reseller_id".*?<\/div>.*?<\/div>/s', '', $content);
// Generate modal:
$content = preg_replace('/<div[^>]*>.*?<label[^>]*>Reseller.*?<\/label>.*?wire:model="reseller_id".*?<\/div>.*?<\/div>/s', '', $content);

file_put_contents($dst, $content);
echo "Created voucher.blade.php\n";
?>
