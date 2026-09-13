<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'color' => 'slate',
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
    'label' => '',
    'color' => 'slate',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = [
        'slate' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
        'blue' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700',
        'amber' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700',
        'red' => 'bg-red-100 dark:bg-red-900/50 text-red-700',
    ];
?>

<span <?php echo e($attributes->merge(['class' => 'inline-flex rounded-full px-2 py-0.5 text-xs font-medium ' . ($classes[$color] ?? $classes['slate'])])); ?>>
    <?php echo e($label); ?>

</span>
<?php /**PATH D:\dsBilling\resources\views\components\chip.blade.php ENDPATH**/ ?>