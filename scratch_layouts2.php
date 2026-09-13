<?php
function checkFile($file) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    $ifs = preg_match_all("/@if\b/", $content);
    $endifs = preg_match_all("/@endif\b/", $content);
    $hasSection = preg_match_all("/@hasSection\b/", $content);
    $show = preg_match_all("/@show\b/", $content);
    $diff = ($ifs + $hasSection) - ($endifs + $show);
    if ($diff !== 0) {
        echo "MISMATCH: $file => @if:$ifs @endif:$endifs @hasSection:$hasSection @show:$show diff:$diff\n";
    } else {
        echo "OK: $file\n";
    }
}
$layouts = glob("resources/views/layouts/*.blade.php");
foreach ($layouts as $f) checkFile($f);
