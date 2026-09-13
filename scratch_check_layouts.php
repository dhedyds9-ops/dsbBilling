<?php
function checkBladeFile($file) {
    $content = file_get_contents($file);
    $ifs = preg_match_all("/@if\b/", $content);
    $endifs = preg_match_all("/@endif\b/", $content);
    $hasSection = preg_match_all("/@hasSection\b/", $content);
    $show = preg_match_all("/@show\b/", $content);
    $diff = ($ifs + $hasSection) - ($endifs + $show);
    if ($diff !== 0) {
        echo "MISMATCH: $file => combined diff: $diff (if:$ifs endif:$endifs hasSection:$hasSection show:$show)\n";
    }
    return $diff;
}

// Check all layouts
$total = 0;
$layoutFiles = array_merge(
    glob("resources/views/layouts/*.blade.php"),
    glob("resources/views/components/layouts/*.blade.php"),
    glob("resources/views/components/admin/*.blade.php"),
    glob("resources/views/components/base/*.blade.php")
);
foreach ($layoutFiles as $f) {
    $d = checkBladeFile($f);
    if ($d !== 0) $total += $d;
}
echo "Total mismatch: $total\n";
