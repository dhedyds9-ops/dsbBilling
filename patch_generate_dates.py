file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Generate.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public $month;
    public $year;
    public $notes;"""
    
replace = """    public $month;
    public $year;
    public $period_start;
    public $period_end;
    public $notes;"""

if search in content:
    content = content.replace(search, replace)
    
    search2 = """    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
    }"""
    replace2 = """    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
        // Default cutoff: 26 prev month to 25 current month
        $this->period_start = date('Y-m-26', strtotime('-1 month'));
        $this->period_end = date('Y-m-25');
    }"""
    content = content.replace(search2, replace2)
    
    search3 = """        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
        ]);"""
    replace3 = """        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
        ]);"""
    content = content.replace(search3, replace3)
    
    search4 = """                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'base_salary' => $employee->base_salary,"""
    replace4 = """                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'period_start' => $this->period_start ?: null,
                        'period_end' => $this->period_end ?: null,
                        'base_salary' => $employee->base_salary,"""
    content = content.replace(search4, replace4)

    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Generate.php modified")
else:
    print("Search block not found.")
