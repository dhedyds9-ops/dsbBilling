import sys

file_web = 'D:/dsBilling/routes/web.php'
with open(file_web, 'r', encoding='utf-8') as f:
    content = f.read()

search = """        Route::get('/technician-portal/attendance', \\App\\Livewire\\ISP\\Technician\\Attendance\\Index::class)->name('technician.attendance');"""

replace = """        Route::get('/technician-portal/attendance', \\App\\Livewire\\ISP\\Technician\\Attendance\\Index::class)->name('technician.attendance');
        
        // Payroll
        Route::get('/technician-portal/payroll', \\App\\Livewire\\ISP\\Technician\\Payroll\\Index::class)->name('technician.payroll.index');
        Route::get('/technician-portal/payroll/{id}', \\App\\Livewire\\ISP\\Technician\\Payroll\\Show::class)->name('technician.payroll.show');"""

if search in content:
    content = content.replace(search, replace)
    with open(file_web, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Routes added")
else:
    print("Search block not found.")
