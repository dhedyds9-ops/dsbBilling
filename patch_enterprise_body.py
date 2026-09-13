file_blade = 'D:/dsBilling/resources/views/layouts/enterprise.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """class="min-h-screen font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-900\""""
replace = """class="min-h-screen font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-900 print:bg-white print:text-black\""""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added print:bg-white to body in enterprise layout")
else:
    print("Search block not found.")
