<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$route = <<<'PHP'
Route::post('/pengaturan/perusahaan/upload-logo', function (\Illuminate\Http\Request $request) {
    $request->validate(['logo' => 'required|image|max:2048']);
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->storePublicly('company/logo', 'public');
        app(\App\Services\Pengaturan\CompanySettingsService::class)->save(['logo_url' => \Illuminate\Support\Facades\Storage::url($path)]);
    }
    return back()->with('success', 'Logo berhasil diperbarui!');
})->name('pengaturan.perusahaan.upload-logo');
PHP;

if (!str_contains($content, 'upload-logo')) {
    file_put_contents($file, $content . "\n" . $route . "\n");
}
echo "Added direct upload route.\n";
