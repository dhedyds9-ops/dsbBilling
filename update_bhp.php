<?php
$file = 'D:/dsBilling/resources/views/livewire/keuangan/bhp-uso/index.blade.php';
$content = file_get_contents($file);

// Replace page_title section
$newTitle = <<<HTML
  @section('page_title')
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-orange-500" translate="no" style="font-size:24px">receipt_long</span>
            <span class="text-lg">Laporan BHP & USO</span>
        </div>
  @endsection
HTML;
$content = preg_replace('/@section\(\'page_title\'\).*?@endsection/is', $newTitle, $content);

file_put_contents($file, $content);
echo "Updated bhp-uso";
?>
