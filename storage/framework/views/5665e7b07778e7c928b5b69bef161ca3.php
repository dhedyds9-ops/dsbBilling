

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'duration' => 5000,
    'dismissible' => true,
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
    'type' => 'info',
    'title' => null,
    'message' => null,
    'duration' => 5000,
    'dismissible' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$variants = [
    'info' => [
        'bg' => 'bg-info-50',
        'border' => 'border-info-200',
        'icon' => 'text-info-500',
        'title' => 'text-info-800',
        'text' => 'text-info-700',
    ],
    'success' => [
        'bg' => 'bg-success-50',
        'border' => 'border-success-200',
        'icon' => 'text-success-500',
        'title' => 'text-success-800',
        'text' => 'text-success-700',
    ],
    'warning' => [
        'bg' => 'bg-warning-50',
        'border' => 'border-warning-200',
        'icon' => 'text-warning-500',
        'title' => 'text-warning-800',
        'text' => 'text-warning-700',
    ],
    'danger' => [
        'bg' => 'bg-danger-50',
        'border' => 'border-danger-200',
        'icon' => 'text-danger-500',
        'title' => 'text-danger-800',
        'text' => 'text-danger-700',
    ],
];

$icons = [
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];

$toastId = 'toast-' . uniqid();
?>

<div
    x-data="{
        show: true,
        duration: <?php echo e($duration); ?>,
        init() {
            if (this.duration > 0) {
                setTimeout(() => this.show = false, this.duration);
            }
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="flex items-start gap-3 w-full max-w-sm p-4 rounded-xl border shadow-soft-lg <?php echo e($variants[$type]['bg']); ?> <?php echo e($variants[$type]['border']); ?>"
    role="alert"
    <?php echo e($attributes); ?>

>
    
    <svg class="w-5 h-5 flex-shrink-0 <?php echo e($variants[$type]['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php echo $icons[$type]; ?>

    </svg>

    
    <div class="flex-1 min-w-0">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
            <p class="font-semibold <?php echo e($variants[$type]['title']); ?>"><?php echo e($title); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <p class="<?php echo e($title ? 'mt-1' : ''); ?> text-sm <?php echo e($variants[$type]['text']); ?>">
            <?php echo e($message ?? $slot); ?>

        </p>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissible): ?>
        <button
            type="button"
            @click="show = false"
            class="flex-shrink-0 p-1 rounded hover:bg-black/5 transition-colors <?php echo e($variants[$type]['text']); ?>"
            aria-label="Dismiss"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\feedback\toast.blade.php ENDPATH**/ ?>