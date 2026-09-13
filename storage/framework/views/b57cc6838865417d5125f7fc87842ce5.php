<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default', // default, primary, success, warning, danger, info
    'size' => 'md', // sm, md
    'rounded' => 'full' // full, md, lg
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
    'variant' => 'default', // default, primary, success, warning, danger, info
    'size' => 'md', // sm, md
    'rounded' => 'full' // full, md, lg
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseClasses = 'inline-flex items-center font-semibold';
    
    $sizeClasses = [
        'sm' => 'px-1.5 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm'
    ][$size] ?? 'px-2.5 py-0.5 text-xs';

    $roundedClasses = [
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full'
    ][$rounded] ?? 'rounded-full';

    $variantClasses = [
        'default' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'primary' => 'bg-blue-100 text-primary-700 dark:bg-blue-900/40 dark:text-blue-300',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'danger' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'info' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    ][$variant] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $roundedClasses . ' ' . $variantClasses;
?>

<span <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php echo e($slot); ?>

</span>






<?php /**PATH D:\dsBilling\resources\views\components\ui\badge.blade.php ENDPATH**/ ?>