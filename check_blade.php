<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

if (!mb_check_encoding($content, 'UTF-8')) {
    echo "The BLADE FILE ITSELF has bad UTF-8!\n";
    
    // Find where
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (!mb_check_encoding($line, 'UTF-8')) {
            echo "Bad byte on line " . ($i+1) . "\n";
            echo "Line hex: " . bin2hex($line) . "\n";
        }
    }
} else {
    echo "Blade file is valid UTF-8.\n";
}
?>
