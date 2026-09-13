<?php
$files = [
    'D:/dsBilling/resources/views/livewire/isp/router/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/device/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/task/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/alarm/index.blade.php',
    'D:/dsBilling/resources/views/livewire/acs/firmware/index.blade.php'
];

$replacements = [
    '/class="p-1\.5 rounded-[a-z]+ text-slate-400 hover:text-indigo-600 hover:bg-indigo-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors"',
    '/class="p-1\.5 rounded-[a-z]+ text-slate-500 hover:text-indigo-600 hover:bg-indigo-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors"',
    '/class="p-1\.5 rounded-[a-z]+ text-slate-400 hover:text-emerald-600 hover:bg-emerald-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors"',
    '/class="p-1\.5 rounded-[a-z]+ text-slate-500 hover:text-emerald-600 hover:bg-emerald-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors"',
    '/class="p-1\.5 rounded-[a-z]+ text-slate-400 hover:text-rose-600 hover:bg-rose-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors"',
    '/class="p-1\.5 rounded-[a-z]+ text-slate-500 hover:text-rose-600 hover:bg-rose-50[^"]*"/' => 'class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors"',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($replacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        file_put_contents($file, $content);
    }
}
echo "Done replacing action button classes.";
?>
