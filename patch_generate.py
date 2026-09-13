file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Generate.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re
search = """                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('period_month', $this->month)
                    ->where('period_year', $this->year)
                    ->exists();

                if (!$exists) {"""

replace = """                $monthInt = (int) $this->month;
                $yearInt = (int) $this->year;
                
                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year', $yearInt)
                    ->exists();

                if (!$exists) {"""

if search in content:
    content = content.replace(search, replace)
    
    # Also update the create array
    search2 = """                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $this->month,
                        'period_year' => $this->year,"""
    replace2 = """                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,"""
    content = content.replace(search2, replace2)
    
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed type casting issue in Payroll Generate")
else:
    print("Search block not found.")
