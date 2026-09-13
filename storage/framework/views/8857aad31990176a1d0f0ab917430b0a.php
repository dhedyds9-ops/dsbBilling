<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'underline',
    'selected' => null,
    'wireModel' => null,
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
    'variant' => 'underline',
    'selected' => null,
    'wireModel' => null,
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
$variants = [
    'underline' => [
        'container' => 'border-b border-[var(--ds-outline-variant)] gap-1',
        'tabBase' => 'pb-3 px-4 -mb-px border-b-2 border-transparent text-sm font-medium transition-colors',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:border-[var(--ds-outline)]',
        'tabActive' => 'text-[var(--ds-primary)] border-[var(--ds-primary)]',
    ],
    'pills' => [
        'container' => 'gap-1 p-1 bg-[var(--ds-surface-container-low)] rounded-2xl',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container)]',
        'tabActive' => 'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md',
    ],
    'segmented' => [
        'container' => 'gap-0.5 p-0.5 bg-[var(--ds-surface-container-low)] rounded-2xl border border-[var(--ds-outline-variant)]',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)]',
        'tabActive' => 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-sm',
    ],
    'boxed' => [
        'container' => 'gap-1 p-1 bg-[var(--ds-surface-container-low)] rounded-2xl',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)]',
        'tabActive' => 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-md border border-[var(--ds-outline-variant)]',
    ],
];

$v = $variants[$variant] ?? $variants['underline'];
?>

<div
    x-data="{
        selectedTab: <?php if($wireModel): ?> <?php if ((object) ($wireModel) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($wireModel->value()); ?>')<?php echo e($wireModel->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($wireModel); ?>')<?php endif; ?> <?php else: ?> '<?php echo e($selected ?? ''); ?>' <?php endif; ?>
    }"
    <?php echo e($attributes->except(['wire:model', 'wire:model.live', 'wire:model.blur'])); ?>

>
    
    <div role="tablist" class="inline-flex w-full flex-wrap items-center <?php echo e($v['container']); ?>">
        <?php echo e($tabs); ?>

    </div>

    
    <div class="mt-5">
        <?php echo e($panels ?? ''); ?>

    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\navigation\tabs.blade.php ENDPATH**/ ?>