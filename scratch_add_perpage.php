<?php
$files = [
    'resources/views/livewire/billing/invoice/index.blade.php',
    'resources/views/livewire/billing/payment/index.blade.php'
];

$replacement = <<<BLADE
                <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-2">
                    <option value="10">10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
                <button wire:click="export"
BLADE;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('<button wire:click="export"', $replacement, $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
