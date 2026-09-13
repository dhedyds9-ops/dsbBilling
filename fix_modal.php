<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/pppoe.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    // Find the Import Modal and delete it
    $startStr = '{{-- IMPORT MODAL --}}';
    $endStr = '    {{-- DELETE CONFIRMATION MODAL --}}';
    
    $start = strpos($content, $startStr);
    $end = strpos($content, $endStr);
    
    if ($start !== false && $end !== false) {
        $content = substr_replace($content, '', $start, $end - $start);
        file_put_contents($file, $content);
        echo "Fixed import modal in $file\n";
    }
}
?>
