<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

// Remove BOM if present
$bom = pack('H*','EFBBBF');
$content = preg_replace("/^$bom/", '', $content);

file_put_contents($file, $content);
echo "Removed BOM if present\n";
