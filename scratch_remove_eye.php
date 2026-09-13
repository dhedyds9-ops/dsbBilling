<?php
$file = 'resources/views/livewire/billing/payment/index.blade.php';
$content = file_get_contents($file);

$pattern = '/@else\s*<a href="\{\{\s*route\(\'billing\.payments\.show\'.*?<\/a>\s*@endif/s';
$replacement = <<<BLADE
@else
        <span class="text-xs text-slate-400 italic">Tanpa Invoice</span>
    @endif
BLADE;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Removed eye icon from payment index\n";
