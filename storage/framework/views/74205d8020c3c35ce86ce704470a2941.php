<?php $__env->startSection('header_title', 'Informasi'); ?>

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)]">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-4">Pusat Informasi & Notifikasi</h3>
        
        <div class="space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full <?php echo e($notif->type === 'system' ? 'bg-blue-100 dark:bg-blue-900/50 text-primary-600' : 'bg-teal-100 dark:bg-teal-900/50 text-teal-600'); ?> flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined"><?php echo e($notif->type === 'system' ? 'campaign' : 'notifications'); ?></span>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($notif->title); ?></h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo e($notif->message); ?></p>
                    <span class="text-[10px] text-slate-400 mt-1 block"><?php echo e($notif->created_at->diffForHumans()); ?></span>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="text-center py-6">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada informasi atau notifikasi.</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <div class="mt-4">
            <?php echo e($notifications->links('pagination::tailwind')); ?>

        </div>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\info.blade.php ENDPATH**/ ?>