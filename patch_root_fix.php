<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php';
$content = file_get_contents($file);

// Move the <script> block inside the root div
if (preg_match('/<script data-navigate-once>.*?<\/script>/s', $content, $matches)) {
    $script = $matches[0];
    $content = str_replace($script, '', $content);
    
    // find the last </div>
    $pos = strrpos($content, '</div>');
    if ($pos !== false) {
        $content = substr_replace($content, "\n" . $script . "\n</div>", $pos, 6);
    }
    
    file_put_contents($file, $content);
    echo "Moved <script> inside root div.\n";
} else {
    echo "Script not found.\n";
}
?>
