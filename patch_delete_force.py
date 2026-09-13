file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "Payroll::findOrFail($id)->delete();"
replace = "Payroll::withTrashed()->findOrFail($id)->forceDelete();"

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Delete method updated to force delete")
else:
    print("Search block not found.")
