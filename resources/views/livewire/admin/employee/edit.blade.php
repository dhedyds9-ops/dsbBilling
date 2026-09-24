<div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Edit Employee</h2>
        <a href="/admin/employee" class="text-gray-500 dark:text-slate-300 hover:underline">Back to List</a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NIK -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">NIK <span class="text-red-500">*</span></label>
                <input type="text" wire:model="nik" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('nik') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Name -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Position -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Position (Job Title)</label>
                <input type="text" wire:model="position" placeholder="e.g. Senior Network Engineer" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('position') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Department -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Department (Job Function)</label>
                <select wire:model="department" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Ada --</option>
                    @foreach(\App\Enums\JobFunction::cases() as $jobFunc)
                        <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                    @endforeach
                </select>
                @error('department') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Phone -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Phone</label>
                <input type="text" wire:model="phone" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('phone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Email</label>
                <input type="email" wire:model="email" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Join Date -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Join Date</label>
                <input type="date" wire:model="join_date" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('join_date') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Base Salary -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Base Salary</label>
                <input type="number" wire:model="base_salary" step="0.01" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('base_salary') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Bank Name -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Bank Name</label>
                <input type="text" wire:model="bank_name" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('bank_name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Bank Account -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Bank Account</label>
                <input type="text" wire:model="bank_account" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('bank_account') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            
            <!-- User ID -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Tautkan Akun Login (Opsional)</label>
                <select wire:model="user_id" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Ditautkan --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id') <span class="text-red-500 text-sm mt-1 block">{{  }}</span> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-slate-300">Status <span class="text-red-500">*</span></label>
                <select wire:model="status" class="w-full px-4 py-2 border rounded dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-8">
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Update Employee</button>
        </div>
    </form>
</div>
