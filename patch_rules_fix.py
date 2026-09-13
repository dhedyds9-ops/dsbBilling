file = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("protected $rules = [", "public function rules() { return [")
content = content.replace("        'user_id' => 'nullable|exists:users,id',\n    ];", "        'user_id' => 'nullable|exists:users,id',\n    ]; }")
with open(file, 'w', encoding='utf-8') as f:
    f.write(content)

file_edit = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("protected function rules()", "// temporarily renaming to avoid conflict\n    protected function oldrules()")
content = content.replace("protected $rules = [", "public function rules() { return [")
content = content.replace("'user_id' => 'nullable|exists:users,id',\n    ];", "'user_id' => 'nullable|exists:users,id',\n    ]; }")
with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed rules method")
