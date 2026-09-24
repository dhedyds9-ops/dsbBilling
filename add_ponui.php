<?php
$files = [
    'resources/views/livewire/isp/onu/index-v2.blade.php',
    'resources/views/livewire/noc/onu/index.blade.php'
];

$ponSelect = <<<HTML
            @if(!empty(\$oltFilter) && \$oltFilter !== 'all')
            <select wire:model.live="ponFilter" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="">All PON</option>
                @foreach(\$ponPorts as \$pon)
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="{{ \$pon->id }}">{{ \$pon->name }}</option>
                @endforeach
            </select>
            @endif
HTML;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Add pr-6 to all select boxes to prevent arrow overlap
    $content = preg_replace('/<select ([^>]*)class="([^"]*px-2 py-1 )([^"]*text-xs)/i', '<select $1class="$2pr-6 $3', $content);
    
    // Insert ponFilter after oltFilter
    $content = preg_replace('/(<\/select>\s*)(<select wire:model\.live="statusFilter")/i', "$1\n$ponSelect\n$2", $content);
    
    file_put_contents($file, $content);
}
echo "Added ponFilter UI and fixed pr-6\n";
