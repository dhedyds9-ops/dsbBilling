<?php
$files = [
    'resources/views/livewire/billing/invoice/index.blade.php',
    'resources/views/livewire/billing/payment/index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('block px-3 py-2', 'block pl-3 pr-8 py-2', $content);
        file_put_contents($file, $content);
        echo "Updated padding in $file\n";
    }
}
