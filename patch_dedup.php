<?php
$files = glob('D:/dsBilling/app/Livewire/ISP/Technician/**/*.php') + glob('D:/dsBilling/app/Livewire/ISP/Technician/*.php');
foreach ($files as $path) {
    $content = file_get_contents($path);
    // Remove duplicate use statements
    $lines = explode("\n", $content);
    $seen = [];
    $cleaned = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^use /', $trimmed)) {
            if (isset($seen[$trimmed])) continue;
            $seen[$trimmed] = true;
        }
        $cleaned[] = $line;
    }
    $content = implode("\n", $cleaned);
    file_put_contents($path, $content);
    echo "Cleaned: $path\n";
}
echo "Done!\n";
?>
