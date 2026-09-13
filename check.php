<?php
$dirs = [
    'resources/views/livewire/reseller-portal',
    'resources/views/livewire/customer-portal',
    'resources/views/livewire/technician',
    'resources/views/livewire/noc',
    'resources/views/livewire/isp',
    'resources/views/livewire/dashboard'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            
            // simple check: if it starts with @if or @foreach and doesn't have a wrapping div
            $trimmed = trim($content);
            
            // To be absolutely safe, let's just use Livewire's own regex or logic
            // Livewire uses: 
            // preg_match_all('/(?:^\s*<([a-zA-Z0-9\-]+)|\G\s*<([a-zA-Z0-9\-]+))/', $html, $matches);
            // We just wrap everything in <div> if we suspect multiple roots.
            // But wait, the user said: "Do NOT perform mass automated wrapping. For every affected component: 1. identify actual root structure..."
            
            // Let's just output files that don't start with <
            if ($trimmed !== '' && $trimmed[0] !== '<') {
                echo "Non-HTML root: " . $file->getPathname() . "\n";
            }
        }
    }
}
