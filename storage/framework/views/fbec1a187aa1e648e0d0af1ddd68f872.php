

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'dismissible' => false,
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
    'size' => 'md',
    'dot' => false,
    'dismissible' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$variants = [
    'default' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
    'primary' => 'bg-primary-100 text-primary-700',
    'secondary' => 'bg-secondary-100 text-secondary-700',
    'success' => 'bg-success-100 text-success-700',
    'warning' => 'bg-warning-100 text-warning-700',
    'danger' => 'bg-danger-100 text-danger-700',
    'info' => 'bg-info-100 text-info-700',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs gap-1',
    'md' => 'px-2.5 py-0.5 text-xs gap-1.5',
    'lg' => 'px-3 py-1 text-sm gap-1.5',
];

$dotColors = [
    'default' => 'bg-slate-400',
    'primary' => 'bg-primary-500',
    'secondary' => 'bg-secondary-500',
    'success' => 'bg-success-500',
    'warning' => 'bg-warning-500',
    'danger' => 'bg-danger-500',
    'info' => 'bg-info-500',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
?>

<span <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dot): ?>
        <span class="<?php echo e($dotColors[$variant]); ?> w-1.5 h-1.5 rounded-full"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo e($slot); ?>


    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissible): ?>
        <button
            type="button"
            @click="$el.parentElement.remove()"
            class="ml-1 -mr-1 p-0.5 rounded-full hover:bg-black/10 transition-colors"
            aria-label="Dismiss"
        >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span>
<?php /**PATH D:\dsBilling\resources\views\components\base\badge.blade.php ENDPATH**/ ?>