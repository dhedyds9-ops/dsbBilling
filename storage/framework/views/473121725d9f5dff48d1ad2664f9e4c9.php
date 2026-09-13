<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                <?php echo e(__('Template Voucher')); ?>

            </h2>
            <div class="flex space-x-2">
                <a href="<?php echo e(route('isp.voucher-templates.import')); ?>" class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">Import</a>
                <a href="<?php echo e(route('isp.voucher-templates.create')); ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">Buat Template</a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100"><?php echo e($template->name); ?></h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($template->category); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($template->is_system): ?>
                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs rounded-full">System</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="flex space-x-2">
                            <a href="<?php echo e(route('isp.voucher-templates.edit', $template->id)); ?>" class="text-slate-500 dark:text-slate-400 hover:text-primary-600">Edit</a>
                            <button wire:click="duplicate(<?php echo e($template->id); ?>)" class="text-slate-500 dark:text-slate-400 hover:text-primary-600">Duplikat</button>
                            <a href="<?php echo e(route('isp.voucher-templates.versions', $template->id)); ?>" class="text-slate-500 dark:text-slate-400 hover:text-primary-600">Versi</a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_system): ?>
                                <button wire:click="delete(<?php echo e($template->id); ?>)" class="text-red-500 hover:text-red-700">Hapus</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex space-x-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_system): ?>
                                <button wire:click="toggleActive(<?php echo e($template->id); ?>)" class="<?php echo e($template->is_active ? 'text-green-600' : 'text-slate-400'); ?>" title="Toggle Aktif">
                                    <span class="material-symbols-outlined text-sm"><?php echo e($template->is_active ? 'toggle_on' : 'toggle_off'); ?></span>
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_default && $template->is_active): ?>
                                <button wire:click="setAsDefault(<?php echo e($template->id); ?>)" class="text-primary-600 hover:text-blue-900" title="Jadikan Default">
                                    <span class="material-symbols-outlined text-sm">star</span>
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="col-span-full bg-white dark:bg-slate-800 p-8 text-center text-slate-500 dark:text-slate-400 rounded-lg shadow">
                Tidak ada template yang ditemukan.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    <div class="mt-6">
        <?php echo e($templates->links()); ?>

    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views/livewire/isp/voucher-template/index.blade.php ENDPATH**/ ?>