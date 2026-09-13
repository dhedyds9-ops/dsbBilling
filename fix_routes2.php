<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/pppoe.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Fix crm.customers.show
    $content = str_replace(
        "route('crm.customers.show', \$user->customer->id)", 
        "route('reseller-portal.customers.show', \$user->customer->id)"
        , $content
    );
    
    file_put_contents($file, $content);
}
echo "Fixed crm routes.\n";
?>
