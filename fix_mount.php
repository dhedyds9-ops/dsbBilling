<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Edit.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Customer360.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('public function mount($id)', 'public function mount($id = null)', $content);
    file_put_contents($file, $content);
}
echo "Fixed mount signature.\n";
?>
