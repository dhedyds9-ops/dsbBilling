<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

$search1 = <<<HTML
              <input type="file" wire:model="partnerLogoFile" id="partnerLogoUpload" class="hidden dark:bg-slate-900 dark:text-slate-100">
              <label for="partnerLogoUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih Logo Mitra
              </label>
HTML;

$replace1 = <<<HTML
              <form action="{{ route('pengaturan.perusahaan.upload-logo') }}?type=partner" method="POST" enctype="multipart/form-data" class="inline-block">
                @csrf
                <input type="file" name="logo" id="partnerLogoUploadNative" class="hidden" onchange="this.form.submit()">
                <label for="partnerLogoUploadNative" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih & Simpan Logo Mitra
                </label>
              </form>
HTML;

$search2 = <<<HTML
              <input type="file" wire:model="stampFile" id="stampUpload" class="hidden dark:bg-slate-900 dark:text-slate-100">
              <label for="stampUpload" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih Cap
              </label>
HTML;

$replace2 = <<<HTML
              <form action="{{ route('pengaturan.perusahaan.upload-logo') }}?type=stamp" method="POST" enctype="multipart/form-data" class="inline-block">
                @csrf
                <input type="file" name="logo" id="stampUploadNative" class="hidden" onchange="this.form.submit()">
                <label for="stampUploadNative" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">upload</span>Pilih & Simpan Cap
                </label>
              </form>
HTML;

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);

// Ensure the success message is shown
$successHtml = <<<HTML
  <!-- Tampilkan error validasi jika ada -->
  @if(session('success'))
    <div class="m-4 p-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
      <div class="font-bold flex items-center gap-2">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">check_circle</span>
        {{ session('success') }}
      </div>
    </div>
  @endif
HTML;
$content = str_replace('<!-- Tampilkan error validasi jika ada -->', $successHtml, $content);

file_put_contents($file, $content);

// Update route to handle types
$routeFile = 'routes/web.php';
$routeContent = file_get_contents($routeFile);
$newRoute = <<<'PHP'
Route::post('/pengaturan/perusahaan/upload-logo', function (\Illuminate\Http\Request $request) {
    $request->validate(['logo' => 'required|image|max:2048']);
    if ($request->hasFile('logo')) {
        $type = $request->query('type', 'logo');
        $key = 'logo_url';
        $prefix = 'company/logo';
        if ($type === 'partner') { $key = 'partner_logo_url'; $prefix = 'company/partner'; }
        if ($type === 'stamp') { $key = 'stamp_url'; $prefix = 'company/stamp'; }
        
        $path = $request->file('logo')->storePublicly($prefix, 'public');
        app(\App\Services\Pengaturan\CompanySettingsService::class)->save([$key => \Illuminate\Support\Facades\Storage::url($path)]);
    }
    return back()->with('success', 'Gambar berhasil diperbarui!');
})->name('pengaturan.perusahaan.upload-logo');
PHP;

$routeContent = preg_replace('/Route::post\(\'\/pengaturan\/perusahaan\/upload-logo\'.*?\}\)->name\(\'pengaturan\.perusahaan\.upload-logo\'\);/s', $newRoute, $routeContent);
file_put_contents($routeFile, $routeContent);

echo "Fully bypassed livewire for all 3 images.\n";
