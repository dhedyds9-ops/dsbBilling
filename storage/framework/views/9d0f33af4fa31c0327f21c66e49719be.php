<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary', // primary, secondary, danger, warning, success, outline, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'href' => null,
    'type' => 'button',
    'loading' => false,
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
    'variant' => 'primary', // primary, secondary, danger, warning, success, outline, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'href' => null,
    'type' => 'button',
    'loading' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-60 disabled:cursor-not-allowed';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-4 py-2 text-sm rounded-lg gap-2',
        'lg' => 'px-6 py-2.5 text-base rounded-xl gap-2',
    ][$size] ?? 'px-4 py-2 text-sm rounded-lg gap-2';

    $variantClasses = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm border border-transparent focus:ring-primary-500',
        'secondary' => 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 shadow-sm focus:ring-primary-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-sm border border-transparent focus:ring-red-500',
        'warning' => 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm border border-transparent focus:ring-amber-500',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm border border-transparent focus:ring-emerald-500',
        'soft' => 'bg-slate-100 dark:bg-slate-700 hover:bg-primary-50 dark:hover:bg-primary-900/30 text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 border border-transparent focus:ring-primary-500',
        'outline' => 'bg-transparent text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 focus:ring-primary-500',
        'ghost' => 'bg-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 focus:ring-slate-500 border border-transparent',
    ][$variant] ?? 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm border border-transparent focus:ring-primary-500';

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
    
    // Default loading state logic for Livewire if loading prop is passed
    if ($loading) {
        $attributes = $attributes->merge(['wire:loading.attr' => 'disabled', 'wire:loading.class' => 'opacity-75 cursor-wait']);
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon && $iconPosition === 'left'): ?>
            <span class="material-symbols-outlined text-[1.2em]"><?php echo e($icon); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php echo e($slot); ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon && $iconPosition === 'right'): ?>
            <span class="material-symbols-outlined text-[1.2em]"><?php echo e($icon); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon && $iconPosition === 'left'): ?>
            <span class="material-symbols-outlined text-[1.2em]"><?php echo e($icon); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php echo e($slot); ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon && $iconPosition === 'right'): ?>
            <span class="material-symbols-outlined text-[1.2em]"><?php echo e($icon); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>






<?php /**PATH D:\dsBilling\resources\views\components\ui\button.blade.php ENDPATH**/ ?>