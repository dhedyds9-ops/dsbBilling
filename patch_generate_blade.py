file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/generate.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>"""

replace = """                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai (Cutoff Start)</label>
                    <input type="date" wire:model="period_start" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('period_start') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir (Cutoff End)</label>
                    <input type="date" wire:model="period_end" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('period_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Generate blade modified")
else:
    print("Search block not found.")
