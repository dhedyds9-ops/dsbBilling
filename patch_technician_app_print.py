file_blade = 'D:/dsBilling/resources/views/layouts/technician-app.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    <!-- App Shell (max-w-md like Customer Portal) -->
    <div class="max-w-md mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 flex flex-col">"""

replace = """    <!-- App Shell (max-w-md like Customer Portal) -->
    <div class="max-w-md print:max-w-none mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 print:bg-white shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 print:border-none print:shadow-none flex flex-col">"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added print:max-w-none to technician app layout")
else:
    print("Search block not found.")
