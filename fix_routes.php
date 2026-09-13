<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/pppoe.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Fix Tambah button
    $content = str_replace(
        "route('reseller-portal.customers.show', 0)", 
        "route('reseller-portal.customers.create')"
        , $content
    );
    
    // Fix Edit button (PPPoE/Hotspot user id vs Customer id)
    $content = str_replace(
        "route('reseller-portal.customers.show', \$user->id)", 
        "route('reseller-portal.customers.show', \$user->customer->id ?? 0)"
        , $content
    );
    
    file_put_contents($file, $content);
    echo "Fixed routes in $file\n";
}
?>
