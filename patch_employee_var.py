file_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function mount($id)
    {
        $this->employee = \\App\\Models\\Employee::findOrFail($id);
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'data-pegawai');
        $this->nik = $employee->nik;"""

replace = """    public function mount($id)
    {
        $this->employee = \\App\\Models\\Employee::findOrFail($id);
        $employee = $this->employee;
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'data-pegawai');
        $this->nik = $employee->nik;"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed $employee undefined variable")
else:
    print("Search block not found.")
