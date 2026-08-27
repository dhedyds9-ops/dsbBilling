

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
    'padding' => true,
    'hover' => false,
    'flush' => false,
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
    'variant' => 'default',
    'padding' => true,
    'hover' => false,
    'flush' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'bg-white dark:bg-slate-800 rounded-xl overflow-hidden';

$variants = [
    'default' => 'border border-slate-200 dark:border-slate-700',
    'bordered' => 'border-2 border-slate-300 dark:border-slate-600',
    'shadow' => 'shadow-soft border border-slate-200 dark:border-slate-700',
    'soft' => 'shadow-soft-lg border border-slate-100 dark:border-slate-700',
];

$classes = $baseClasses . ' ' . $variants[$variant];

if ($hover) {
    $classes .= ' transition-all duration-200 hover:shadow-soft-md hover:border-slate-300 dark:hover:border-slate-600';
}

if ($flush) {
    $classes .= ' rounded-none';
}
?>

<div <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 <?php echo e($padding ? '' : 'px-0 py-0'); ?>">
            <?php echo e($header); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="<?php echo e($padding ? 'px-6 py-4' : ''); ?>">
        <?php echo e($slot); ?>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 <?php echo e($padding ? '' : 'px-0 py-0'); ?>">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\base\card.blade.php ENDPATH**/ ?>