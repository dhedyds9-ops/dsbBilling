
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $status = strtolower($status);
    $variant = 'default';
    
    if (in_array($status, ['active', 'online', 'paid', 'success', 'completed', 'verified'])) {
        $variant = 'success';
    } elseif (in_array($status, ['inactive', 'offline', 'unpaid', 'failed', 'suspended', 'error'])) {
        $variant = 'danger';
    } elseif (in_array($status, ['pending', 'processing', 'warning', 'waiting', 'hold'])) {
        $variant = 'warning';
    } elseif (in_array($status, ['info', 'draft', 'new'])) {
        $variant = 'info';
    } elseif (in_array($status, ['expired', 'cancelled'])) {
        $variant = 'default';
    }

    $label = strtoupper($status);
?>

<?php if (isset($component)) { $__componentOriginala35a025426a524afee759497a5cc40ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala35a025426a524afee759497a5cc40ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.badge','data' => ['variant' => ''.e($variant).'','dot' => 'true','attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($variant).'','dot' => 'true','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php echo e($slot->isEmpty() ? $label : $slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala35a025426a524afee759497a5cc40ac)): ?>
<?php $attributes = $__attributesOriginala35a025426a524afee759497a5cc40ac; ?>
<?php unset($__attributesOriginala35a025426a524afee759497a5cc40ac); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala35a025426a524afee759497a5cc40ac)): ?>
<?php $component = $__componentOriginala35a025426a524afee759497a5cc40ac; ?>
<?php unset($__componentOriginala35a025426a524afee759497a5cc40ac); ?>
<?php endif; ?>






<?php /**PATH D:\dsBilling\resources\views\components\base\status-badge.blade.php ENDPATH**/ ?>