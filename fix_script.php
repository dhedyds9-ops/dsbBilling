<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php';
$content = file_get_contents($file);

// Ensure the @script block is BEFORE the final </div>
// Let's just find the @script block and move it.
if (preg_match('/@script.*?@endscript/s', $content, $matches)) {
    $scriptBlock = $matches[0];
    $content = str_replace($scriptBlock, '', $content);
    
    // Find the last </div>
    $pos = strrpos($content, '</div>');
    if ($pos !== false) {
        $content = substr_replace($content, "\n" . $scriptBlock . "\n</div>", $pos, 6);
    }
    
    file_put_contents($file, $content);
    echo "Fixed @script position robustly.\n";
} else {
    echo "Could not find @script block.\n";
}
?>
