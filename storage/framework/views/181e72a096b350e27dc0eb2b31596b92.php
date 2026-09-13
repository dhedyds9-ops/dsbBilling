

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
    'selected' => null,
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
    'selected' => null,
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
    'default' => [
        'container' => 'border-b border-slate-200 dark:border-slate-700',
        'tab' => 'text-slate-600 hover:text-slate-900 dark:text-slate-100 border-b-2 border-transparent',
        'tabActive' => 'text-primary-600 border-primary-600',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'pills' => [
        'container' => 'gap-2',
        'tab' => 'px-4 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800',
        'tabActive' => 'bg-primary-600 text-white',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'underline' => [
        'container' => 'border-b border-slate-200 dark:border-slate-700 gap-8',
        'tab' => 'pb-3 text-slate-600 border-b-2 border-transparent hover:text-slate-900 dark:text-slate-100 hover:border-slate-300 dark:border-slate-600',
        'tabActive' => 'text-primary-600 border-primary-600',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'boxed' => [
        'container' => 'p-1 bg-slate-100 dark:bg-slate-800 rounded-xl gap-1',
        'tab' => 'px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-100',
        'tabActive' => 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
];

$currentVariant = $variants[$variant];
?>

<div x-data="{ selected: <?php if ((object) ($attributes->get('wire:model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->get('wire:model')->value()); ?>')<?php echo e($attributes->get('wire:model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->get('wire:model')); ?>')<?php endif; ?> || '<?php echo e($selected); ?>' }" <?php echo e($attributes->except(['wire:model'])); ?>>
    
    <div class="<?php echo e($variant !== 'pills' ? 'flex' : 'flex flex-wrap'); ?> <?php echo e($currentVariant['container']); ?>">
        <?php echo e($tabs); ?>

    </div>

    
    <div class="mt-4">
        <?php echo e($panels); ?>

    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\layout\tabs.blade.php ENDPATH**/ ?>