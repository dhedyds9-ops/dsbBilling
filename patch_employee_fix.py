import re
import os

file_create_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file_create_php, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("\\ = \\App\\Models\\User::all();", "$users = \\App\\Models\\User::all();")
with open(file_create_php, 'w', encoding='utf-8') as f:
    f.write(content)

file_edit_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit_php, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("\\ = \\App\\Models\\User::all();", "$users = \\App\\Models\\User::all();")
with open(file_edit_php, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed PHP files")
