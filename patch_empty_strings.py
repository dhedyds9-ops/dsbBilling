file_create = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file_create, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("'join_date' => $this->join_date,", "'join_date' => $this->join_date ?: null,")
content = content.replace("'base_salary' => $this->base_salary,", "'base_salary' => $this->base_salary ?: 0,")
content = content.replace("'department' => $this->department,", "'department' => $this->department ?: null,")
with open(file_create, 'w', encoding='utf-8') as f:
    f.write(content)

file_edit = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("'join_date' => $this->join_date,", "'join_date' => $this->join_date ?: null,")
content = content.replace("'base_salary' => $this->base_salary,", "'base_salary' => $this->base_salary ?: 0,")
content = content.replace("'department' => $this->department,", "'department' => $this->department ?: null,")
with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed empty string handling")
