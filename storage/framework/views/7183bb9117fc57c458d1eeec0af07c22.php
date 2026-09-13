<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
    'height' => 320,
    'toolbar' => null,
    'legend' => null,
    'footer' => null,
    'noPadding' => false,
    'bodyClass' => '',
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
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
    'height' => 320,
    'toolbar' => null,
    'legend' => null,
    'footer' => null,
    'noPadding' => false,
    'bodyClass' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$iconBgMap = [
    'primary'   => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
    'success'   => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
    'warning'   => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
    'danger'    => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
    'info'      => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
    'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-300',
    'neutral'   => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
];
?>

<div <?php echo e($attributes->merge(['class' => 'rounded-2xl border border-[var(--ds-outline-variant)] bg-[var(--ds-surface)] shadow-soft-sm overflow-hidden flex flex-col'])); ?>>
    
    <div class="flex flex-wrap items-start justify-between gap-4 px-6 py-5 border-b border-[var(--ds-outline-variant)]">
        <div class="flex items-start gap-3 min-w-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
                <div class="w-10 h-10 flex-shrink-0 rounded-xl <?php echo e($iconBgMap[$iconBg] ?? $iconBgMap['primary']); ?> inline-flex items-center justify-center">
                    <span class="material-symbols-outlined fill weight-500 ms-20"><?php echo e($icon); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                    <h3 class="text-[15px] font-semibold text-[var(--ds-on-surface)] leading-tight"><?php echo e($title); ?></h3>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                    <p class="text-xs text-[var(--ds-on-surface-variant)] mt-1 leading-relaxed"><?php echo e($subtitle); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($headerMeta)): ?>
                    <div class="mt-2"><?php echo e($headerMeta); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="flex flex-wrap items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($legend): ?>
                <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--ds-on-surface-variant)] mr-2">
                    <?php echo $legend; ?>

                </div>
            <?php elseif(isset($legendSlot)): ?>
                <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--ds-on-surface-variant)] mr-2">
                    <?php echo e($legendSlot); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($toolbar): ?>
                <?php echo $toolbar; ?>

            <?php elseif(isset($toolbarSlot)): ?>
                <?php echo e($toolbarSlot); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actions)): ?>
                <?php echo e($actions); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'relative flex-1 w-full',
        'p-0' => $noPadding,
        'p-5' => !$noPadding,
        $bodyClass,
    ]); ?>" style="min-height: <?php echo e($height); ?>px;">
        <?php echo e($slot); ?>

    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($footer || isset($footerSlot)): ?>
        <div class="px-6 py-3.5 border-t border-[var(--ds-outline-variant)] bg-[var(--ds-surface-container-low)] text-xs text-[var(--ds-on-surface-variant)]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($footer): ?><?php echo e($footer); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footerSlot)): ?><?php echo e($footerSlot); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\charts\chart-container.blade.php ENDPATH**/ ?>