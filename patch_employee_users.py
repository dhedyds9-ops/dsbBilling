file_create = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file_create, 'r', encoding='utf-8') as f:
    content = f.read()
replacement = """$users = \\App\\Models\\User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();"""
content = content.replace("$users = \\App\\Models\\User::all();", replacement)
with open(file_create, 'w', encoding='utf-8') as f:
    f.write(content)

file_edit = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("$users = \\App\\Models\\User::all();", replacement)
with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated queries")
