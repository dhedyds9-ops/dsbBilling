import re

# Update Create.php
file_create = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file_create, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("'department' => 'nullable|string|max:255',", "'department' => ['nullable', new \\Illuminate\\Validation\\Rules\\Enum(\\App\\Enums\\JobFunction::class)],")
with open(file_create, 'w', encoding='utf-8') as f:
    f.write(content)

# Update Edit.php
file_edit = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("'department' => 'nullable|string|max:255',", "'department' => ['nullable', new \\Illuminate\\Validation\\Rules\\Enum(\\App\\Enums\\JobFunction::class)],")
with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated PHP rules")
