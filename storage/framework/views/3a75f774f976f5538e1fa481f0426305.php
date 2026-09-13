

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'rows' => 4,
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'placeholder' => '',
    'label' => null,
    'hint' => null,
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
    'rows' => 4,
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'placeholder' => '',
    'label' => null,
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'w-full border rounded-lg transition-all duration-200 placeholder-slate-400 focus:outline-none focus:ring-2 resize-y';

$stateClasses = $error
    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
    : ($success
        ? 'border-success-500 focus:border-success-500 focus:ring-success-500/20'
        : 'border-slate-300 dark:border-slate-600 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-slate-800');

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '';

$classes = $baseClasses . ' ' . $stateClasses . ' ' . $disabledClasses;
?>

<div class="space-y-1.5">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            <?php echo e($label); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attributes->has('required')): ?>
                <span class="text-danger-500">*</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <textarea
        rows="<?php echo e($rows); ?>"
        <?php echo e($attributes->merge(['class' => $classes])); ?>

        placeholder="<?php echo e($placeholder); ?>"
        <?php if($disabled): ?> disabled <?php endif; ?>
        <?php if($readonly): ?> readonly <?php endif; ?>
    ><?php echo e($slot); ?></textarea>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <p class="text-sm text-danger-600"><?php echo e($error); ?></p>
    <?php elseif($hint): ?>
        <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($hint); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\forms\textarea.blade.php ENDPATH**/ ?>