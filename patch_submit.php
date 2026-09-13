<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('wire:submit.prevent="save"', 'wire:submit="save"', $content);
    file_put_contents($file, $content);
}
echo "Changed wire:submit.\n";
?>
