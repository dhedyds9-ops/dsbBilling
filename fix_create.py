file = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("'status' => 'required|string|in:active,inactive',\n    ];", "'status' => 'required|string|in:active,inactive',\n        'user_id' => 'nullable|exists:users,id',\n    ]; }")

with open(file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed Create")
