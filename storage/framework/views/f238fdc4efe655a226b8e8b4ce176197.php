<div <?php echo e($attributes->merge(['class' => 'rounded border flex flex-col'])); ?> style="background-color: var(--noc-panel); border-color: var(--noc-border);">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
        <div class="px-4 py-3 border-b flex items-center justify-between flex-none" style="border-color: var(--noc-border);">
            <?php echo e($header); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <div class="flex-1 overflow-auto <?php echo e($noPadding ?? false ? '' : 'p-4'); ?>">
        <?php echo e($slot); ?>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
        <div class="px-4 py-3 border-t flex-none" style="border-color: var(--noc-border); background-color: var(--noc-subpanel);">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\noc\card.blade.php ENDPATH**/ ?>