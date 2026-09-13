<div
    <?php echo e($attributes->class([
        'relative bg-ds-surface border border-slate-200 dark:border-slate-700 rounded-2xl shadow-soft-sm overflow-hidden',
    ])); ?>

>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
        <div
            class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                   border-b border-slate-200 dark:border-slate-700 bg-ds-surface-container-low/40"
        >
            <?php echo e($header); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <?php echo e($slot); ?>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
        <div
            class="px-4 sm:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-ds-surface-container-low/40"
        >
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\admin\table-card.blade.php ENDPATH**/ ?>