file_php = 'D:/dsBilling/routes/web.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "Route::prefix('isp')->name('isp.')->group(function () {"
routes = """Route::prefix('isp')->name('isp.')->group(function () {
            // Voucher Templates
            Route::get('/voucher-templates', \\App\\Livewire\\ISP\\VoucherTemplate\\Index::class)->name('voucher-templates.index');
            Route::get('/voucher-templates/create', \\App\\Livewire\\ISP\\VoucherTemplate\\Editor::class)->name('voucher-templates.create');
            Route::get('/voucher-templates/import', \\App\\Livewire\\ISP\\VoucherTemplate\\Import::class)->name('voucher-templates.import');
            Route::get('/voucher-templates/{id}/edit', \\App\\Livewire\\ISP\\VoucherTemplate\\Editor::class)->name('voucher-templates.edit');
            Route::get('/voucher-templates/{id}/preview', \\App\\Livewire\\ISP\\VoucherTemplate\\Preview::class)->name('voucher-templates.preview');
            Route::get('/voucher-templates/{id}/versions', \\App\\Livewire\\ISP\\VoucherTemplate\\Versions::class)->name('voucher-templates.versions');
"""

content = content.replace(search, routes)
with open(file_php, 'w', encoding='utf-8') as f:
    f.write(content)
print("Voucher template routes added!")
