<?php
$file = 'D:/dsBilling/resources/views/livewire/acs/device/index.blade.php';
$content = file_get_contents($file);
$content = preg_replace('/<a href="\{\{ route\(\'acs\.devices\.create\'\).*?Tambah\s*<\/a>/is', '', $content);
file_put_contents($file, $content);
echo "Removed from UI.";
?>
