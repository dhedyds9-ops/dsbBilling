file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/show.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search1 = """<div class="flex justify-between items-start border-b-2 border-slate-800 pb-4 mb-6 print:border-black">"""
replace1 = """<div class="flex flex-col md:flex-row justify-between items-start border-b-2 border-slate-800 pb-4 mb-6 print:flex-row print:border-black gap-4">"""

search2 = """<div class="text-right">"""
replace2 = """<div class="text-left md:text-right print:text-right w-full md:w-auto">"""

search3 = """<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 text-[13px] sm:text-sm print:grid-cols-2 print:text-black">"""
replace3 = """<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-[13px] md:text-sm print:grid-cols-2 print:text-black">"""

search4 = """<div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8 print:grid-cols-2">"""
replace4 = """<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 print:grid-cols-2">"""

# Ensure w-full is used on tables but they don't break
search5 = """<td class="py-1 text-slate-500 print:text-slate-800 w-32 font-medium">"""
replace5 = """<td class="py-1 text-slate-500 print:text-slate-800 w-28 md:w-32 font-medium">"""


if search1 in content:
    content = content.replace(search1, replace1)
    content = content.replace(search2, replace2)
    content = content.replace(search3, replace3)
    content = content.replace(search4, replace4)
    content = content.replace(search5, replace5)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Blade patched for responsiveness")
else:
    print("Search block not found.")
