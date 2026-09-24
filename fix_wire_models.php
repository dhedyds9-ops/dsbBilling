<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

$content = str_replace('wire:model="logoUpload"', 'wire:model="logoFile"', $content);
$content = str_replace('wire:model="stampUpload"', 'wire:model="stampFile"', $content);

file_put_contents($file, $content);
echo "Fixed wire:model bindings\n";
