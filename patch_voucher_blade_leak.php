<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/sales/voucher.blade.php';
$content = file_get_contents($file);

// Remove the Owner/Reseller dropdown entirely
$pattern = '/<!-- Owner -->\s*<div>\s*<label[^>]*>Owner \/ Reseller<\/label>\s*<select wire:model="reseller_id"[^>]*>.*?<\/select>\s*<\/div>/is';
$content = preg_replace($pattern, '', $content);

file_put_contents($file, $content);
echo "Removed reseller dropdown from blade.\n";
?>
