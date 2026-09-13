<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

$content = str_replace('$this->cachedDeviceParams = null;', '', $content);

file_put_contents($file, $content);
echo "Removed $this->cachedDeviceParams = null";
?>
