<?php
$file = 'resources/views/livewire/isp/olt/index.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    "route('isp.onus.index', ['oltFilter' => \$olt->id])",
    "route('isp.onus.index', ['oltFilter' => \$olt->id, 'statusFilter' => 'online'])",
    $content
);

file_put_contents($file, $content);
echo "Replaced blade link\n";
