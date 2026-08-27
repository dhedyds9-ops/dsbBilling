

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'size' => 'md',
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
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$sizes = [
    'sm' => ['toggle' => 'w-8 h-4', 'dot' => 'h-3 w-3', 'translate' => 'translate-x-4', 'base' => ''],
    'md' => ['toggle' => 'w-11 h-6', 'dot' => 'h-5 w-5', 'translate' => 'translate-x-5', 'base' => ''],
    'lg' => ['toggle' => 'w-14 h-7', 'dot' => 'h-6 w-6', 'translate' => 'translate-x-7', 'base' => ''],
];

$toggleClasses = $sizes[$size]['toggle'];
$dotClasses = $sizes[$size]['dot'];
$translateClasses = $sizes[$size]['translate'];
?>

<div class="flex items-center gap-3 <?php echo e($disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'); ?>" <?php echo e($attributes->only(['wire:model', 'wire:model.lazy', 'x-model'])); ?>>
    <button
        type="button"
        role="switch"
        :aria-checked="<?php echo e($checked); ?>"
        @click="!<?php echo e($disabled); ?> && $refs.toggle.click()"
        class="<?php echo e($toggleClasses); ?> relative inline-flex items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 <?php echo e($checked ? 'bg-primary-600' : 'bg-slate-200'); ?>"
        x-ref="toggle"
        <?php if($disabled): ?> disabled <?php endif; ?>
    >
        <span
            class="inline-block <?php echo e($dotClasses); ?> transform rounded-full bg-white shadow-sm transition-transform duration-200 ease-in-out <?php echo e($checked ? $translateClasses : 'translate-x-0.5'); ?>"
        ></span>
    </button>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <span class="text-sm font-medium text-slate-700"><?php echo e($label); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <input
        type="checkbox"
        class="sr-only"
        <?php echo e($attributes->merge(['class' => ''])); ?>

        <?php if($checked): ?> checked <?php endif; ?>
        <?php if($disabled): ?> disabled <?php endif; ?>
    />
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\forms\toggle.blade.php ENDPATH**/ ?>