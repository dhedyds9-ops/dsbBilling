<?php
function checkDir($dir) {
    $files = [];
    if (!is_dir($dir)) return $files;
    foreach (glob($dir . "/*.blade.php") as $f) $files[] = $f;
    foreach (glob($dir . "/*", GLOB_ONLYDIR) as $subdir) {
        $files = array_merge($files, checkDir($subdir));
    }
    return $files;
}
$files = checkDir("resources/views/components");
foreach ($files as $file) {
    $content = file_get_contents($file);
    $ifs = preg_match_all("/@if\b/", $content);
    $endifs = preg_match_all("/@endif\b/", $content);
    if ($ifs !== $endifs) {
        echo "MISMATCH: $file => @if:$ifs @endif:$endifs diff:".($ifs-$endifs)."\n";
    }
}
echo "Done checking components.\n";
