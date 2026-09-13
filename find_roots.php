<?php
$dirs = [
    'resources/views/livewire/reseller-portal',
    'resources/views/livewire/customer-portal',
    'resources/views/livewire/technician',
    'resources/views/livewire/noc',
    'resources/views/livewire/isp',
    'resources/views/livewire/dashboard'
];

function hasMultipleRoots($content) {
    // Basic heuristic: find the first HTML tag.
    // If it's a structural tag, we expect it to close at the very end of the file.
    $content = trim(preg_replace('/<!--.*?-->/s', '', $content)); // remove comments
    $content = trim(preg_replace('/@(?:push|prepend)\(.*?\).*?@end(?:push|prepend)/s', '', $content));
    
    // Check if the content starts with an HTML tag
    if (preg_match('/^<([a-zA-Z0-9\-]+)[^>]*>/s', $content, $matches)) {
        $tag = $matches[1];
        // self closing tags don't count as wrappers
        if (in_array(strtolower($tag), ['img', 'input', 'br', 'hr', 'meta', 'link'])) return true;
        
        // Count opening and closing tags of the same type? Too complex.
        // Let's just check if the last thing is the closing tag of the first thing.
        if (preg_match('/<\/([a-zA-Z0-9\-]+)>\s*$/s', $content, $endMatches)) {
            // This is a rough heuristic, it might fail if there's text after the closing tag
            // But let's check for multiple roots directly
            // A better way: check if there's a tag followed by another tag at root level
            // For example: </div> <div>
        }
    }
    
    // A simpler regex: find </...>\s*<... at the root level? Impossible with regex.
    return false;
}

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $content = trim(preg_replace('/<!--.*?-->/s', '', $content));
            
            // Heuristic: Does it start with a div/section and end with a div/section?
            $lines = explode("\n", $content);
            $lastLine = trim(end($lines));
            
            // If we see </div>\n<div in the file, it might be multiple roots, but it could be nested.
            // Let's just output files that don't start with a single <div or <section or <main or <x-
            
            if (!preg_match('/^<(?:div|section|main|header|footer|form|table|ul|nav|x-[a-zA-Z0-9\-\.]+)[^>]*>/i', $content)) {
                echo "Warning: No root wrapper found? " . $file->getPathname() . "\n";
            } else {
                // If the first tag doesn't match the last tag roughly
                preg_match('/^<([a-zA-Z0-9\-\.]+)/i', $content, $m1);
                preg_match('/<\/([a-zA-Z0-9\-\.]+)>$/i', $content, $m2);
                if (isset($m1[1]) && isset($m2[1]) && strtolower($m1[1]) !== strtolower($m2[1])) {
                    if (strtolower($m1[1]) !== 'x-slot' && strtolower($m2[1]) !== 'x-slot') {
                        echo "Warning: Tag mismatch or multiple roots? " . $file->getPathname() . " (" . $m1[1] . " vs " . $m2[1] . ")\n";
                    }
                }
            }
        }
    }
}
