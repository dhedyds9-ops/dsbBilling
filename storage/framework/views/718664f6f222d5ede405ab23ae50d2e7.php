<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status' => null]));

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

foreach (array_filter((['status' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $label = trim((string) $status);
    $normalized = strtolower($label);
    $variants = [
        'active' => 'success', 'online' => 'success', 'up' => 'success', 'connected' => 'success', 'available' => 'success', 'success' => 'success',
        'pending' => 'warning', 'warning' => 'warning', 'maintenance' => 'warning', 'degraded' => 'warning',
        'inactive' => 'danger', 'offline' => 'danger', 'down' => 'danger', 'disconnected' => 'danger', 'failed' => 'danger', 'error' => 'danger',
    ];
    $variant = $variants[$normalized] ?? 'default';
?>

<?php if (isset($component)) { $__componentOriginala35a025426a524afee759497a5cc40ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala35a025426a524afee759497a5cc40ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.badge','data' => ['variant' => $variant,'size' => 'sm','dot' => $variant !== 'default']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant),'size' => 'sm','dot' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant !== 'default')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php echo e($label !== '' ? ucfirst(str_replace('_', ' ', $label)) : '-'); ?>

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
<?php /**PATH D:\dsBilling\resources\views\components\status-badge.blade.php ENDPATH**/ ?>