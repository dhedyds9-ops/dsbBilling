<?php
$file = "app/Traits/HasResellerScope.php";
$content = file_get_contents($file);
if (substr($content, 0, 3) === "\xef\xbb\xbf") {
    file_put_contents($file, substr($content, 3));
    echo "BOM removed.\n";
} else {
    echo "No BOM found.\n";
}

