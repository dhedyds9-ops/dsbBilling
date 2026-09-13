<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Strip all non-ascii bytes
$content = preg_replace('/[\x80-\xFF]/', '', $content);

// Ensure it has &copy;
$content = str_replace('attribution: \'&copy; OpenStreetMap', 'attribution: \'&copy; OpenStreetMap', $content);

file_put_contents($file, $content);
echo "Stripped all non-ASCII bytes from Blade file.\n";
?>
