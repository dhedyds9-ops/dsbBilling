<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php';
$content = file_get_contents($file);

// Find the <div class="md:col-span-2"> that contains wire:model="reseller_id" and remove it
$content = preg_replace('/<div class="md:col-span-2">\s*<label[^>]*>Owner \/ Reseller Mitra<\/label>.*?<\/div>/s', '', $content);

file_put_contents($file, $content);
echo "Removed reseller dropdown from hotspot.\n";
?>
