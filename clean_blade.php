<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Replace any invalid UTF-8 sequence with valid UTF-8
// specifically targeting the bad copyright symbol
$content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');

// Let's also just replace the copyright symbol with &copy;
$content = str_replace('©', '&copy;', $content);

file_put_contents($file, $content);
echo "Cleaned Blade file encoding!";
?>
