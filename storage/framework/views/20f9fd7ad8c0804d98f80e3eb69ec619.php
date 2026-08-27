<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'val' => 0,
    'color' => 'blue',
]));

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

foreach (array_filter(([
    'val' => 0,
    'color' => 'blue',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variantMap = [
        'emerald' => 'success',
        'amber' => 'warning',
        'red' => 'danger',
        'blue' => 'info',
        'slate' => 'primary',
    ];
?>

<?php if (isset($component)) { $__componentOriginal24b82eae6b4d39b1280fac5951f4b991 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24b82eae6b4d39b1280fac5951f4b991 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.progress','data' => ['value' => $val,'variant' => $variantMap[$color] ?? 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.progress'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($val),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variantMap[$color] ?? 'primary'),'size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24b82eae6b4d39b1280fac5951f4b991)): ?>
<?php $attributes = $__attributesOriginal24b82eae6b4d39b1280fac5951f4b991; ?>
<?php unset($__attributesOriginal24b82eae6b4d39b1280fac5951f4b991); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24b82eae6b4d39b1280fac5951f4b991)): ?>
<?php $component = $__componentOriginal24b82eae6b4d39b1280fac5951f4b991; ?>
<?php unset($__componentOriginal24b82eae6b4d39b1280fac5951f4b991); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\progress-bar.blade.php ENDPATH**/ ?>