file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "->when($this->month, fn($q) => $q->where('period_month', $this->month))"
replace = "->when($this->month, fn($q) => $q->where('period_month', (int) $this->month))"

if search in content:
    content = content.replace(search, replace)
    
    search2 = "->when($this->year, fn($q) => $q->where('period_year', $this->year))"
    replace2 = "->when($this->year, fn($q) => $q->where('period_year', (int) $this->year))"
    content = content.replace(search2, replace2)
    
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed type casting issue in Payroll Index")
else:
    print("Search block not found.")
