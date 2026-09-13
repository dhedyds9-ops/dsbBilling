file_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

# Just restore it to how it was, but modify the mount to accept $id instead of Employee $employee
import re

search = """    public function mount($id)
    {
        $this->employee = \\App\\Models\\Employee::findOrFail($id);
        $this->nik = $this->employee->nik;
        $this->name = $this->employee->name;
        $this->position = $this->employee->position;
        $this->department = $this->employee->department;
        $this->phone = $this->employee->phone;
        $this->email = $this->employee->email;
        $this->join_date = $this->employee->join_date ? $this->employee->join_date->format('Y-m-d') : null;
        $this->base_salary = $this->employee->base_salary;
        $this->bank_name = $this->employee->bank_name;
        $this->bank_account = $this->employee->bank_account;
        $this->status = $this->employee->status;
        $this->user_id = $this->employee->user_id;
        
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'data-pegawai');
    }"""
    
content = content.replace(search, "") # Remove my injected mount

# Now modify the original mount
search2 = """    public function mount(Employee $employee)
    {
        $this->employee = $employee;"""
        
replace2 = """    public function mount($id)
    {
        $this->employee = \\App\\Models\\Employee::findOrFail($id);
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'data-pegawai');"""

if search2 in content:
    content = content.replace(search2, replace2)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed Edit.php mount method correctly")
else:
    print("Search2 block not found.")
