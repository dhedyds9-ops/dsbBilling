<div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Employees</h2>
        <a href="/admin/employee/create" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Add Employee</a>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-100 rounded">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Search by name or NIK..." class="w-full md:w-1/3 px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200">
                    <th class="p-3 border-b dark:border-slate-600">NIK</th>
                    <th class="p-3 border-b dark:border-slate-600">Name</th>
                    <th class="p-3 border-b dark:border-slate-600">Position</th>
                    <th class="p-3 border-b dark:border-slate-600">Department</th>
                    <th class="p-3 border-b dark:border-slate-600">Status</th>
                    <th class="p-3 border-b dark:border-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                        <td class="p-3 text-gray-800 dark:text-slate-200">{{ $employee->nik }}</td>
                        <td class="p-3 text-gray-800 dark:text-slate-200">{{ $employee->name }}</td>
                        <td class="p-3 text-gray-800 dark:text-slate-200">{{ $employee->position }}</td>
                        <td class="p-3 text-gray-800 dark:text-slate-200">{{ $employee->department }}</td>
                        <td class="p-3 text-gray-800 dark:text-slate-200">
                            <span class="px-2 py-1 text-sm rounded {{ $employee->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="/admin/employee/{{ $employee->id }}/edit" class="text-blue-500 hover:underline mr-3">Edit</a>
                            <button wire:click="delete({{ $employee->id }})" wire:confirm="Are you sure you want to delete this employee?" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500 dark:text-slate-400">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</div>
