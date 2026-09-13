<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // For success swal
    $content = str_replace(
        "icon: 'success',",
        "icon: 'success',\n                    width: '28em',\n                    customClass: { popup: 'rounded-xl shadow-xl' },",
        $content
    );
    
    // For error swal
    $content = str_replace(
        "icon: 'warning',",
        "icon: 'warning',\n                    width: '24em',\n                    customClass: { popup: 'rounded-xl shadow-xl' },",
        $content
    );
    
    file_put_contents($file, $content);
}
echo "Shrunk popup.\n";
?>
