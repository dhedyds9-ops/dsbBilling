file_create = 'D:/dsBilling/resources/views/livewire/admin/employee/create.blade.php'
with open(file_create, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Position HTML
search_pos = """<label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Position</label>
                <input type="text" wire:model="position" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">"""
replace_pos = """<label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Position (Job Title)</label>
                <input type="text" wire:model="position" placeholder="e.g. Senior Network Engineer" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">"""
content = content.replace(search_pos, replace_pos)

# Replace Department HTML
search_dep = """<label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Department</label>
                <input type="text" wire:model="department" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">"""
replace_dep = """<label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Department (Job Function)</label>
                <select wire:model="department" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Ada --</option>
                    @foreach(\\App\\Enums\\JobFunction::cases() as $jobFunc)
                        <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                    @endforeach
                </select>"""
content = content.replace(search_dep, replace_dep)

with open(file_create, 'w', encoding='utf-8') as f:
    f.write(content)


file_edit = 'D:/dsBilling/resources/views/livewire/admin/employee/edit.blade.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(search_pos, replace_pos)
content = content.replace(search_dep, replace_dep)

with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated Blade files")
