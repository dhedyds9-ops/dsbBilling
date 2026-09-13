<?php
$dir = "D:/dsBilling/resources/views/livewire/acs";
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach($iter as $file) {
    if($file->isFile() && $file->getExtension() === "php") {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        // Colors
        $content = preg_replace("/\btext-primary-(\d+)\b/", "text-indigo-$1", $content);
        $content = preg_replace("/\bbg-primary-(\d+)\b/", "bg-indigo-$1", $content);
        $content = preg_replace("/\bborder-primary-(\d+)\b/", "border-indigo-$1", $content);
        $content = preg_replace("/\bring-primary-(\d+)\b/", "ring-indigo-$1", $content);
        
        // SVGs
        // add
        $content = preg_replace("/<svg[^>]*>.*?d=\"M12 4v16m8-8H4\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">add</span>', $content);
        $content = preg_replace("/<svg[^>]*>.*?d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">add</span>', $content);
        // save
        $content = preg_replace("/<svg[^>]*>.*?d=\"M5 13l4 4L19 7\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>', $content);
        // edit
        $content = preg_replace("/<svg[^>]*>.*?d=\"M15\.232 5\.232l3\.536 3\.536m-2\.036-5\.036a2\.5 2\.5 0 113\.536 3\.536L6\.5 21\.036H3v-3\.572L16\.732 3\.732z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>', $content);
        // delete
        $content = preg_replace("/<svg[^>]*>.*?d=\"M19 7l-\.867 12\.142A2 2 0 0116\.138 21H7\.862a2 2 0 01-1\.995-1\.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>', $content);
        // search
        $content = preg_replace("/<svg[^>]*>.*?d=\"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">search</span>', $content);
        // filter
        $content = preg_replace("/<svg[^>]*>.*?d=\"M3 4a1 1 0 011-1h16a1 1 0 011 1v2\.586a1 1 0 01-\.293\.707l-6\.414 6\.414a1 1 0 00-\.293\.707V17l-4 4v-6\.586a1 1 0 00-\.293-\.707L3\.293 7\.293A1 1 0 013 6\.586V4z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_list</span>', $content);
        // download/export
        $content = preg_replace("/<svg[^>]*>.*?d=\"M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">download</span>', $content);
        // info
        $content = preg_replace("/<svg[^>]*>.*?d=\"M13 16h-1v-4h-1m1-4h\.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">info</span>', $content);
        // check circle (online)
        $content = preg_replace("/<svg[^>]*>.*?d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">check_circle</span>', $content);
        // cancel/error (offline)
        $content = preg_replace("/<svg[^>]*>.*?d=\"M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">cancel</span>', $content);
        // warning/assignment
        $content = preg_replace("/<svg[^>]*>.*?d=\"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">assignment</span>', $content);
        // upload/arrow-up
        $content = preg_replace("/<svg[^>]*>.*?d=\"M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">upload</span>', $content);
        // link/external
        $content = preg_replace("/<svg[^>]*>.*?d=\"M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\".*?<\/svg>/s", '<span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">open_in_new</span>', $content);

        file_put_contents($path, $content);
    }
}
echo "Done replacing.";
?>
