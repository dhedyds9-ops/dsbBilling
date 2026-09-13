<?php
$files = [
    'D:/dsBilling/resources/views/livewire/acs/device/create.blade.php' => 'acs.devices.index',
    'D:/dsBilling/resources/views/livewire/acs/device/edit.blade.php' => 'acs.devices.index',
    'D:/dsBilling/resources/views/livewire/acs/device/show.blade.php' => 'acs.devices.index',
    'D:/dsBilling/resources/views/livewire/acs/firmware/create.blade.php' => 'acs.firmware.index',
    'D:/dsBilling/resources/views/livewire/acs/firmware/edit.blade.php' => 'acs.firmware.index'
];

foreach ($files as $file => $backRoute) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Remove breadcrumbs
        $content = preg_replace('/<x-admin\.breadcrumbs[^>]*>/', '', $content);
        
        // Replace old header block with new standardized one
        // The old block usually looks like: <div class="flex flex-col..."><h1...>Title</h1>...<a href="..." ...>Kembali</a></div>
        
        // Just extract the h1 text
        preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $m);
        $title = $m ? $m[1] : 'Detail';
        
        // Extract paragraph text for subtitle
        preg_match('/<p[^>]*text-slate-500[^>]*>(.*?)<\/p>/i', $content, $m2);
        $subtitle = $m2 ? $m2[1] : '';
        
        $newHeader = <<<HTML
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3 mb-4">
    <div class="flex items-center gap-3">
      <a href="{{ route('$backRoute') }}" class="p-1.5 rounded-md text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">arrow_back</span>
      </a>
      <div>
        <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">$title</h1>
        <div class="text-xs text-slate-500 dark:text-slate-400">$subtitle</div>
      </div>
    </div>
  </div>
HTML;

        // Replace the whole flex header block with newHeader
        $content = preg_replace('/<div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">.*?<\/div>\s*<\/div>/is', $newHeader, $content);
        
        file_put_contents($file, $content);
    }
}
echo "Done formatting sub pages.";
?>
