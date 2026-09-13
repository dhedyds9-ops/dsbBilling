<div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Employees</h2>
        <a href="/admin/employee/create" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Add Employee</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-100 rounded">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                        <td class="p-3 text-gray-800 dark:text-slate-200"><?php echo e($employee->nik); ?></td>
                        <td class="p-3 text-gray-800 dark:text-slate-200"><?php echo e($employee->name); ?></td>
                        <td class="p-3 text-gray-800 dark:text-slate-200"><?php echo e($employee->position); ?></td>
                        <td class="p-3 text-gray-800 dark:text-slate-200"><?php echo e($employee->department); ?></td>
                        <td class="p-3 text-gray-800 dark:text-slate-200">
                            <span class="px-2 py-1 text-sm rounded <?php echo e($employee->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'); ?>">
                                <?php echo e(ucfirst($employee->status)); ?>

                            </span>
                        </td>
                        <td class="p-3">
                            <a href="/admin/employee/<?php echo e($employee->id); ?>/edit" class="text-blue-500 hover:underline mr-3">Edit</a>
                            <button wire:click="delete(<?php echo e($employee->id); ?>)" wire:confirm="Are you sure you want to delete this employee?" class="text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500 dark:text-slate-400">No employees found.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        <?php echo e($employees->links()); ?>

    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\admin\employee\index.blade.php ENDPATH**/ ?>