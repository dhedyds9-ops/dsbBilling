<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/index.blade.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace("route('reseller-portal.customers.show', 0)", "route('reseller-portal.customers.create')", $content);
    file_put_contents($file, $content);
    echo "Fixed index blade.\n";
}
?>
