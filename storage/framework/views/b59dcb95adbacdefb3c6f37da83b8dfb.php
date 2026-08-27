

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'active' => false,
    'disabled' => false,
    'danger' => false,
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
    'href' => null,
    'active' => false,
    'disabled' => false,
    'danger' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'flex items-center gap-2 w-full px-4 py-2 text-sm text-left transition-colors';

$stateClasses = $disabled
    ? 'text-slate-400 cursor-not-allowed'
    : ($active
        ? 'bg-primary-50 text-primary-700'
        : ($danger
            ? 'text-danger-600 hover:bg-danger-50'
            : 'text-slate-700 hover:bg-slate-100'));

$classes = $baseClasses . ' ' . $stateClasses;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href && !$disabled): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button
        type="button"
        <?php echo e($attributes->merge(['class' => $classes])); ?>

        <?php if($disabled): ?> disabled <?php endif; ?>
    >
        <?php echo e($slot); ?>

    </button>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\base\dropdown-item.blade.php ENDPATH**/ ?>