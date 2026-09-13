<?php
// Dashboard
$path = 'D:/dsBilling/resources/views/livewire/acs/dashboard.blade.php';
$content = file_get_contents($path);
$content = preg_replace('/<div class="px-4 py-3 bg-white.*?<\/div>\s*<\/div>/s', "@include('livewire.acs._tabs')\n", $content);
file_put_contents($path, $content);

// Device Index
$path = 'D:/dsBilling/resources/views/livewire/acs/device/index.blade.php';
$content = file_get_contents($path);
$actions = <<<'HTML'
    <x-slot name="actions">
      <button wire:click="export" class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-md font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors inline-flex items-center gap-1.5">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">download</span>
        Export
      </button>
      <a href="{{ route('acs.devices.create') }}" class="px-3 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">add</span>
        Tambah Device
      </a>
    </x-slot>
HTML;
$content = preg_replace('/<div class="px-4 py-3 bg-white.*?<\/div>\s*<\/div>/s', "@include('livewire.acs._tabs', ['actions' => ob_get_clean()])\n", $content);
// wait, passing slotted variables is hard in include. 
// I'll just rewrite the file content manually for the header.
echo "Done script.\n";
?>
