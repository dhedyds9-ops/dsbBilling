
<?php if (isset($component)) { $__componentOriginal7f18f43b3d2bacb563c64f2b49c801bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f18f43b3d2bacb563c64f2b49c801bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.dropdown','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('trigger', null, []); ?> 
        <button class="p-1 text-slate-400 hover:text-slate-600 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:bg-slate-800 transition-colors">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'more-horizontal','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'more-horizontal','class' => 'w-5 h-5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
        </button>
     <?php $__env->endSlot(); ?>
    
    <div class="py-1">
        <?php echo e($slot); ?>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7f18f43b3d2bacb563c64f2b49c801bc)): ?>
<?php $attributes = $__attributesOriginal7f18f43b3d2bacb563c64f2b49c801bc; ?>
<?php unset($__attributesOriginal7f18f43b3d2bacb563c64f2b49c801bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7f18f43b3d2bacb563c64f2b49c801bc)): ?>
<?php $component = $__componentOriginal7f18f43b3d2bacb563c64f2b49c801bc; ?>
<?php unset($__componentOriginal7f18f43b3d2bacb563c64f2b49c801bc); ?>
<?php endif; ?>






<?php /**PATH D:\dsBilling\resources\views\components\admin\action-dropdown.blade.php ENDPATH**/ ?>