

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'optionLabel' => 'label',
    'optionValue' => 'value',
    'placeholder' => 'Select an option',
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'label' => null,
    'hint' => null,
    'empty' => true,
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
    'options' => [],
    'optionLabel' => 'label',
    'optionValue' => 'value',
    'placeholder' => 'Select an option',
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'label' => null,
    'hint' => null,
    'empty' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'w-full border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 appearance-none bg-no-repeat bg-right pr-10';

$stateClasses = $error
    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
    : ($success
        ? 'border-success-500 focus:border-success-500 focus:ring-success-500/20'
        : 'border-slate-300 dark:border-slate-600 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-slate-800');

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '';

$classes = $baseClasses . ' ' . $stateClasses . ' ' . $disabledClasses . ' px-4 py-2 text-sm';
?>

<div class="space-y-1.5" <?php echo e($attributes->only(['wire:model', 'wire:model.lazy', 'x-model'])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            <?php echo e($label); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attributes->has('required')): ?>
                <span class="text-danger-500">*</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="relative">
        <select
            <?php echo e($attributes->except(['required'])->merge(['class' => $classes])); ?>

            style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-size: 1.5em 1.5em;"
            <?php if($disabled): ?> disabled <?php endif; ?>
            <?php if($readonly): ?> readonly <?php endif; ?>
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empty): ?>
                <option value="" disabled><?php echo e($placeholder); ?></option>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e(is_array($option) ? $option[$optionValue] : $option); ?>">
                    <?php echo e(is_array($option) ? $option[$optionLabel] : $option); ?>

                </option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <?php echo e($optionsSlot ?? ''); ?>

        </select>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <p class="text-sm text-danger-600"><?php echo e($error); ?></p>
    <?php elseif($hint): ?>
        <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($hint); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\forms\select.blade.php ENDPATH**/ ?>