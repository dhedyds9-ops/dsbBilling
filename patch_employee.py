import re
import os

# 1. Update Create.php
file_create_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Create.php'
with open(file_create_php, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("public $status = 'active';", "public $status = 'active';\n    public $user_id;")
content = content.replace("'status' => 'required|in:active,inactive',", "'status' => 'required|in:active,inactive',\n        'user_id' => 'nullable|exists:users,id',")
content = content.replace("'status' => $this->status,", "'status' => $this->status,\n            'user_id' => $this->user_id ?: null,")
content = content.replace("return view('livewire.admin.employee.create');", "\ = \\App\\Models\\User::all();\n        return view('livewire.admin.employee.create', compact('users'));")
with open(file_create_php, 'w', encoding='utf-8') as f:
    f.write(content)

# 2. Update Edit.php
file_edit_php = 'D:/dsBilling/app/Livewire/Admin/Employee/Edit.php'
with open(file_edit_php, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("public $status;", "public $status;\n    public $user_id;")
content = content.replace("$this->status = $employee->status;", "$this->status = $employee->status;\n        $this->user_id = $employee->user_id;")
content = content.replace("'status' => 'required|in:active,inactive',", "'status' => 'required|in:active,inactive',\n        'user_id' => 'nullable|exists:users,id',")
content = content.replace("'status' => $this->status,", "'status' => $this->status,\n            'user_id' => $this->user_id ?: null,")
content = content.replace("return view('livewire.admin.employee.edit');", "\ = \\App\\Models\\User::all();\n        return view('livewire.admin.employee.edit', compact('users'));")
with open(file_edit_php, 'w', encoding='utf-8') as f:
    f.write(content)

# 3. Update create.blade.php
file_create_blade = 'D:/dsBilling/resources/views/livewire/admin/employee/create.blade.php'
with open(file_create_blade, 'r', encoding='utf-8') as f:
    content = f.read()

html_to_add = '''
            <!-- User ID -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Tautkan Akun Login (Opsional)</label>
                <select wire:model="user_id" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Ditautkan --</option>
                    @foreach( as )
                        <option value="{{ ->id }}">{{ ->name }} ({{ ->email }})</option>
                    @endforeach
                </select>
                @error('user_id') <span class="text-red-500 text-sm mt-1 block">{{  }}</span> @enderror
            </div>
'''
content = content.replace('<!-- Status -->', html_to_add + '\n            <!-- Status -->')
with open(file_create_blade, 'w', encoding='utf-8') as f:
    f.write(content)

# 4. Update edit.blade.php
file_edit_blade = 'D:/dsBilling/resources/views/livewire/admin/employee/edit.blade.php'
with open(file_edit_blade, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('<!-- Status -->', html_to_add + '\n            <!-- Status -->')
with open(file_edit_blade, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added user_id link successfully.")
