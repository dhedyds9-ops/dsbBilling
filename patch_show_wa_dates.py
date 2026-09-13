file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Show.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """            $month = Carbon::createFromDate($this->payroll->period_year, $this->payroll->period_month, 1)->translatedFormat('F Y');"""
replace = """            if ($this->payroll->period_start && $this->payroll->period_end) {
                $month = Carbon::parse($this->payroll->period_start)->translatedFormat('d M Y') . ' - ' . Carbon::parse($this->payroll->period_end)->translatedFormat('d M Y');
            } else {
                $month = Carbon::createFromDate($this->payroll->period_year, $this->payroll->period_month, 1)->translatedFormat('F Y');
            }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Show php WA logic modified")
else:
    print("Search block not found.")
