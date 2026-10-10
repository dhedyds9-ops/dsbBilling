<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$topBarStart = '<!-- TopBar / Header Section -->';
$topBarEnd = '<!-- Tab Contents -->';

$posStart = strpos($content, $topBarStart);
$posEnd = strpos($content, $topBarEnd);

if ($posStart !== false && $posEnd !== false) {
    // Extract top bar
    $topBarBlock = substr($content, $posStart, $posEnd - $posStart);
    
    // Remove it from the original place
    $content = substr_replace($content, '', $posStart, $posEnd - $posStart);
    
    // Insert it inside the overview tab
    $overviewTabStart = '@if( === \'overview\')';
    $posOverview = strpos($content, $overviewTabStart);
    if ($posOverview !== false) {
        // Insert right after the overview tab start, plus the opening div "mt-6" (wait, mt-6 wraps all tabs)
        // Let's insert it inside the if block
        $insertPos = $posOverview + strlen($overviewTabStart) + 1; // +1 for newline
        $content = substr_replace($content, $topBarBlock . "\n", $insertPos, 0);
    }
}

file_put_contents($file, $content);
