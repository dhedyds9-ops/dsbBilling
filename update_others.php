<?php
$files = [
    'D:/dsBilling/resources/views/livewire/acs/task/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/alarm/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/firmware/index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Ensure tabs are included if not present
        if (strpos($content, "@include('livewire.acs._tabs')") === false) {
            $content = "<div>\n  @include('livewire.acs._tabs')\n\n  <div class=\"space-y-5 pb-10\">\n    <!-- MIGRATED CONTENT -->\n" . $content . "\n  </div>\n</div>";
        }
        
        // Remove old headers and breadcrumbs
        $content = preg_replace('/<x-admin\.breadcrumbs[^>]*>/', '', $content);
        $content = preg_replace('/<div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">.*?<\/div>\s*<\/div>/is', '', $content);
        
        // Replace x-base.card with standard div
        $content = preg_replace('/<x-base\.card>/', '<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">', $content);
        $content = preg_replace('/<\/x-base\.card>/', '</div>', $content);
        
        file_put_contents($file, $content);
    }
}
echo "Done replacing.";
?>
