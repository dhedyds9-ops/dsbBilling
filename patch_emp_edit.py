file_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    public function rules()
    {"""

replace = """    public function mount($id)
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
    }

    public function rules()
    {"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Employee Edit mount method added")
else:
    print("Search block not found.")
