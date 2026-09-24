<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

$content = preg_replace(
    '/<input type="file" wire:model="partnerLogoFile".*?<\/label>/s',
    '<form action="{{ route(\'pengaturan.perusahaan.upload-logo\') }}?type=partner" method="POST" enctype="multipart/form-data" class="inline-block">
                @csrf
                <input type="file" name="logo" id="partnerLogoUploadNative" class="hidden" onchange="this.form.submit()">
                <label for="partnerLogoUploadNative" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih & Simpan Logo Mitra
                </label>
              </form>',
    $content
);

$content = preg_replace(
    '/<input type="file" wire:model="stampFile".*?<\/label>/s',
    '<form action="{{ route(\'pengaturan.perusahaan.upload-logo\') }}?type=stamp" method="POST" enctype="multipart/form-data" class="inline-block">
                @csrf
                <input type="file" name="logo" id="stampUploadNative" class="hidden" onchange="this.form.submit()">
                <label for="stampUploadNative" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih & Simpan Cap
                </label>
              </form>',
    $content
);

file_put_contents($file, $content);
echo "Partner and stamp files replaced.\n";
