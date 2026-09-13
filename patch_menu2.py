filepath = 'D:/dsBilling/app/Navigation/MenuRegistry.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("'admin.employees.index'", "'admin.employee.index'")
content = content.replace("'admin.employees.*'", "'admin.employee.*'")
content = content.replace("'admin.payrolls.index'", "'admin.payroll.index'")
content = content.replace("'admin.payrolls.*'", "'admin.payroll.*'")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
