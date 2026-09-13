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
    $class = match (strtoupper($status)) {
        'ONLINE', 'ACTIVE', 'SUCCESS', 'OK' => 'noc-badge-online',
        'WARNING'                           => 'noc-badge-warning',
        'OFFLINE', 'CRITICAL', 'DOWN'       => 'noc-badge-offline',
        'LOS', 'LOW_RX'                     => 'noc-badge-los',
        'INFO', 'RESOLVED'                  => 'noc-badge-info',
        default                             => 'noc-badge-unknown',
    };
?>

<span <?php echo e($attributes->merge(['class' => "px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap $class"])); ?>>
    <?php echo e($slot->isEmpty() ? $status : $slot); ?>

</span>






<?php /**PATH D:\dsBilling\resources\views\components\noc\stat-badge.blade.php ENDPATH**/ ?>