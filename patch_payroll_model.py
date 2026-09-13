file_php = 'D:/dsBilling/app/Models/Payroll.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """        'employee_id', 'period_month', 'period_year',
        'base_salary', 'allowances', 'deductions', """
        
replace = """        'employee_id', 'period_month', 'period_year',
        'period_start', 'period_end',
        'base_salary', 'allowances', 'deductions', """

if search in content:
    content = content.replace(search, replace)
    
    search2 = """        'period_year' => 'integer',
        'base_salary' => 'decimal:2',"""
    replace2 = """        'period_year' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'base_salary' => 'decimal:2',"""
    content = content.replace(search2, replace2)
    
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Model modified")
else:
    print("Search block not found.")
