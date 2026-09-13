<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php';
$content = file_get_contents($file);

// Move the @script block inside the closing </div>
$searchStr = "</div>\n\n@script";
$replaceStr = "@script";

$content = str_replace($searchStr, $replaceStr, $content);
$content = str_replace("@endscript\n", "@endscript\n</div>\n", $content);

file_put_contents($file, $content);
echo "Moved @script inside root div.\n";
?>
