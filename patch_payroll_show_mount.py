file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Show.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function mount(Payroll $payroll)
    {
        $this->payroll = $payroll;
        $this->payroll->load('employee');
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'payroll');
    }"""
    
replace = """    public function mount($id)
    {
        $this->payroll = Payroll::with('employee')->findOrFail($id);
        $this->dispatch('set-active-menu', module: 'kepegawaian', page: 'payroll');
    }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed Payroll Show mount method")
else:
    print("Search block not found.")
