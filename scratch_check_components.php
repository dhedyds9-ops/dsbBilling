<?php
// Count @if/@endif in ALL component files used in this blade
$files = glob("resources/views/components/**/*.blade.php", GLOB_BRACE);
$files = array_merge($files, glob("resources/views/components/*.blade.php"));
$issues = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    $ifs = preg_match_all("/@if\b/", $content);
    $endifs = preg_match_all("/@endif/", $content);
    if ($ifs !== $endifs) {
        $issues[] = ["file" => $file, "ifs" => $ifs, "endifs" => $endifs, "diff" => $ifs - $endifs];
    }
}
if (empty($issues)) {
    echo "All components balanced!\n";
} else {
    foreach ($issues as $i) {
        echo "MISMATCH: {$i['file']} - if:{$i['ifs']} endif:{$i['endifs']} diff:{$i['diff']}\n";
    }
}
