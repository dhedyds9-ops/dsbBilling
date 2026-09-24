<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

$searchBtn = '<button wire:click="save" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">';
$replaceBtn = <<<HTML
<button wire:click="save" wire:loading.attr="disabled" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
HTML;
$content = str_replace($searchBtn, $replaceBtn, $content);

$searchForm = '<div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">';
$replaceForm = <<<HTML
  <!-- Tampilkan error validasi jika ada -->
  @if(\$errors->any())
    <div class="m-4 p-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
      <div class="font-bold mb-1">Gagal menyimpan, silakan periksa kembali:</div>
      <ul class="list-disc pl-5 text-sm">
        @foreach(\$errors->all() as \$err)
          <li>{{ \$err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
HTML;
$content = str_replace($searchForm, $replaceForm, $content);

file_put_contents($file, $content);
echo "Added error display\n";
