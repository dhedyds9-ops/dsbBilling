<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

$content = str_replace("'device' => 'Device',", "'device' => 'Device & Monitor',", $content);
$content = preg_replace("/'monitoring'\s*=>\s*'Monitoring',\r?\n?/", "", $content);

file_put_contents($file, $content);
echo "Updated tabs array.";
?>
