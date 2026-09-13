file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Payslip</a>
                            @if ($payroll->status !== 'paid')
                                <button wire:click="markAsPaid({{ $payroll->id }})" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">Mark Paid</button>
                            @endif"""

replace = """                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Payslip</a>
                            @if ($payroll->status !== 'paid')
                                <a href="{{ route('admin.payroll.edit', $payroll->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">Edit</a>
                                <button wire:click="markAsPaid({{ $payroll->id }})" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">Mark Paid</button>
                            @endif"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added Edit button")
else:
    print("Search block not found.")
