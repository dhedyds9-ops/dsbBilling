

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'info',
    'size' => 'md',
    'icon' => true,
    'dismissible' => false,
    'title' => null,
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
    'variant' => 'info',
    'size' => 'md',
    'icon' => true,
    'dismissible' => false,
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'rounded-xl border';

$variants = [
    'info' => [
        'container' => 'bg-info-50 border-info-200',
        'icon' => 'text-info-500',
        'title' => 'text-info-800',
        'text' => 'text-info-700',
        'close' => 'text-info-400 hover:text-info-600',
    ],
    'success' => [
        'container' => 'bg-success-50 border-success-200',
        'icon' => 'text-success-500',
        'title' => 'text-success-800',
        'text' => 'text-success-700',
        'close' => 'text-success-400 hover:text-success-600',
    ],
    'warning' => [
        'container' => 'bg-warning-50 border-warning-200',
        'icon' => 'text-warning-500',
        'title' => 'text-warning-800',
        'text' => 'text-warning-700',
        'close' => 'text-warning-400 hover:text-warning-600',
    ],
    'danger' => [
        'container' => 'bg-danger-50 border-danger-200',
        'icon' => 'text-danger-500',
        'title' => 'text-danger-800',
        'text' => 'text-danger-700',
        'close' => 'text-danger-400 hover:text-danger-600',
    ],
];

$sizes = [
    'sm' => 'p-3 text-sm',
    'md' => 'p-4 text-sm',
    'lg' => 'p-5 text-base',
];

$icons = [
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];

$classes = $baseClasses . ' ' . $variants[$variant]['container'] . ' ' . $sizes[$size];
?>

<div <?php echo e($attributes->merge(['class' => $classes])); ?> role="alert">
    <div class="flex gap-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 <?php echo e($variants[$variant]['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?php echo $icons[$variant]; ?>

            </svg>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="flex-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                <h4 class="font-semibold <?php echo e($variants[$variant]['title']); ?>"><?php echo e($title); ?></h4>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="<?php echo e($title ? 'mt-1' : ''); ?> <?php echo e($variants[$variant]['text']); ?>">
                <?php echo e($slot); ?>

            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissible): ?>
            <button
                type="button"
                @click="$el.parentElement.parentElement.remove()"
                class="<?php echo e($variants[$variant]['close']); ?> hover:bg-black/5 rounded p-1 transition-colors"
                aria-label="Dismiss"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\feedback\alert.blade.php ENDPATH**/ ?>