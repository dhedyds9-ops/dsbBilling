

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'label' => null,
    'showValue' => false,
    'variant' => 'primary',
    'striped' => false,
    'animated' => false,
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
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'label' => null,
    'showValue' => false,
    'variant' => 'primary',
    'striped' => false,
    'animated' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$percentage = min(100, max(0, ($value / $max) * 100));

$sizes = [
    'sm' => 'h-1',
    'md' => 'h-2',
    'lg' => 'h-3',
];

$variants = [
    'primary' => 'bg-primary-600',
    'success' => 'bg-success-600',
    'warning' => 'bg-warning-500',
    'danger' => 'bg-danger-600',
    'info' => 'bg-info-600',
];

$barClasses = $variants[$variant];

if ($striped) {
    $barClasses .= ' bg-[url(&apos;data:image/svg+xml,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200h20v20H0z%22%20fill%3D%22rgba%28255%2C255%2C255%2C.1%29%22%2F%3E%3Cpath%20d%3D%22M0%2020l10-10m0%200l10-10m-10%2010l10%2010m0%200l10-10%22%20stroke%3D%22rgba%28255%2C255%2C255%2C.05%29%22%20stroke-width%3D%221%22%2F%3E%3C%2Fsvg%3E&apos;)]';
}

if ($animated) {
    $barClasses .= ' animate-pulse';
}
?>

<div class="w-full" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label || $showValue): ?>
        <div class="flex items-center justify-between mb-1.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?php echo e($label); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showValue): ?>
                <span class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(round($percentage)); ?>%</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="w-full bg-slate-200 rounded-full overflow-hidden <?php echo e($sizes[$size]); ?>">
        <div
            class="<?php echo e($barClasses); ?> <?php echo e($sizes[$size]); ?> rounded-full transition-all duration-500 ease-out"
            style="width: <?php echo e($percentage); ?>%"
            role="progressbar"
            :aria-valuenow="<?php echo e($value); ?>"
            aria-valuemin="0"
            aria-valuemax="<?php echo e($max); ?>"
        ></div>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\data-display\progress.blade.php ENDPATH**/ ?>