<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => null,
    'disabled' => false,
    'icon' => null,
    'badge' => null,
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
    'value' => null,
    'disabled' => false,
    'icon' => null,
    'badge' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$variant = 'underline';
$container = $attributes->get('parent-container') ?? '';
?>

<button
    type="button"
    role="tab"
    x-bind:aria-selected="$parent.selectedTab === '<?php echo e($value); ?>'"
    x-bind:tabindex="$parent.selectedTab === '<?php echo e($value); ?>' ? 0 : -1"
    :class="[
        ($parent.selectedTab === '<?php echo e($value); ?>')
            ? ($el.closest('[role=\"tablist\"]').classList.contains('gap-1') && $el.closest('[role=\"tablist\"]').classList.contains('p-1')
                ? 'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md h-9 px-4 rounded-xl inline-flex items-center gap-2 text-sm font-medium transition-all'
                : ($el.closest('[role=\"tablist\"]').classList.contains('-mb-px')
                    ? 'pb-3 px-4 -mb-px border-b-2 border-[var(--ds-primary)] text-[var(--ds-primary)] text-sm font-medium transition-colors inline-flex items-center gap-2'
                    : 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-md h-9 px-4 rounded-xl inline-flex items-center gap-2 text-sm font-medium transition-all border border-[var(--ds-outline-variant)]'
                  )
              )
            : ($el.closest('[role=\"tablist\"]').classList.contains('-mb-px')
                ? 'pb-3 px-4 -mb-px border-b-2 border-transparent text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:border-[var(--ds-outline)] text-sm font-medium transition-colors inline-flex items-center gap-2'
                : 'h-9 px-4 rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container)] text-sm font-medium transition-all inline-flex items-center gap-2'
              )
    , '<?php echo e($disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''); ?>']"
    @click="if (!<?php echo e($disabled ? 'true' : 'false'); ?>) $parent.selectedTab = '<?php echo e($value); ?>'"
    <?php if($disabled): ?> disabled aria-disabled="true" <?php endif; ?>
    <?php echo e($attributes->except(['parent-container', 'class'])); ?>

>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
        <span class="material-symbols-outlined ms-18"><?php echo e($icon); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <span><?php echo e($slot); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($badge !== null): ?>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'inline-flex items-center justify-center h-5 px-2 rounded-full text-xs font-semibold',
            'bg-[var(--ds-primary-container)] text-[var(--ds-on-primary-container)]' => true,
        ]); ?>">
            <?php echo e($badge); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</button>






<?php /**PATH D:\dsBilling\resources\views\components\navigation\tab.blade.php ENDPATH**/ ?>