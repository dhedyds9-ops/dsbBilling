file_php = 'D:/dsBilling/routes/web.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "Route::get('/payroll/{id}', \\App\\Livewire\\Admin\\Payroll\\Show::class)->name('payroll.show');"
replace = "Route::get('/payroll/{id}', \\App\\Livewire\\Admin\\Payroll\\Show::class)->name('payroll.show');\n            Route::get('/payroll/{id}/edit', \\App\\Livewire\\Admin\\Payroll\\Edit::class)->name('payroll.edit');"

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added route")
else:
    print("Search block not found.")
