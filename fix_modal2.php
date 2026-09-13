<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/pppoe.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Quick regex to strip the modal block and the import button
    $content = preg_replace('/\{\{-- IMPORT MODAL --\}\}(.*?)@endif\s+/s', '', $content);
    $content = preg_replace('/<button wire:click="openImportModal".*?<\/button>/s', '', $content);
    $content = preg_replace('/<button wire:click="export".*?<\/button>/s', '', $content); // might as well remove export
    
    file_put_contents($file, $content);
    echo "Cleaned $file\n";
}
?>
