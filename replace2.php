<?php
$dir = "D:/dsBilling/resources/views/livewire/acs";
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$iconMap = [
    'M12 4v16m8-8H4' => 'add',
    'M12 6v6m0 0v6m0-6h6m-6 0H6' => 'add',
    'M5 13l4 4L19 7' => 'save',
    'M15.232 5.232l3.536 3.536' => 'edit',
    'M19 7l-.867 12.142A2 2 0 0116.138 21' => 'delete',
    'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' => 'search',
    'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414' => 'filter_list',
    'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' => 'download',
    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0' => 'info',
    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' => 'check_circle',
    'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z' => 'cancel',
    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2' => 'assignment',
    'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' => 'upload',
    'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14' => 'open_in_new',
    'M4 4v5h.582m15.356 2' => 'refresh',
    'M6 18L18 6M6 6l12 12' => 'close',
    'M15 12a3 3 0 11-6 0' => 'visibility',
    'M9 3v2m6-2v2' => 'router'
];

foreach($iter as $file) {
    if($file->isFile() && $file->getExtension() === "php") {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $content = preg_replace("/\btext-primary-(\d+)\b/", "text-indigo-$1", $content);
        $content = preg_replace("/\bbg-primary-(\d+)\b/", "bg-indigo-$1", $content);
        $content = preg_replace("/\bborder-primary-(\d+)\b/", "border-indigo-$1", $content);
        $content = preg_replace("/\bring-primary-(\d+)\b/", "ring-indigo-$1", $content);
        
        $content = preg_replace_callback("/<svg[^>]*>(?:(?!<svg).)*?<\/svg>/s", function($m) use ($iconMap) {
            $svg = $m[0];
            foreach ($iconMap as $pathFragment => $iconName) {
                if (strpos($svg, $pathFragment) !== false) {
                    return '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">' . $iconName . '</span>';
                }
            }
            return $svg;
        }, $content);

        file_put_contents($path, $content);
    }
}
echo "Done replacing.";
?>
