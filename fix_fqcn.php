<?php
$file = 'resources/views/livewire/acs/device/show.blade.php';
$content = file_get_contents($file);

$search = '<livewire:acs.device.wan-manager :device="$device" />';
$replace = '@livewire(\App\Livewire\ACS\Device\WanManager::class, [\'device\' => $device])';

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.blade.php updated with FQCN for WanManager.\n";
