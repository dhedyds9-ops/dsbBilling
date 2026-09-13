<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Pppoe.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Hotspot.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace('clone $this->baseQuery()', '$this->baseQuery()', $content);
    $content = str_replace('clone $query', '$query', $content);
    file_put_contents($file, $content);
}
echo "Fixed.\n";
?>
