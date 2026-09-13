<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => null,
    'variant' => 'default',
    'ctaLabel' => null,
    'ctaRoute' => null,
    'ctaIcon' => 'add',
    'ctaAction' => null,
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
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => null,
    'variant' => 'default',
    'ctaLabel' => null,
    'ctaRoute' => null,
    'ctaIcon' => 'add',
    'ctaAction' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$iconBgVariants = [
    'default' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
    'success' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
    'danger'  => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
    'info'    => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
];

$iconBg = $iconBgVariants[$variant] ?? $iconBgVariants['default'];
?>

<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div class="w-20 h-20 rounded-3xl <?php echo e($iconBg); ?> flex items-center justify-center mb-6 shadow-soft">
        <span class="material-symbols-outlined fill weight-500 ms-48"><?php echo e($icon); ?></span>
    </div>

    <h3 class="text-lg font-semibold text-[var(--ds-on-surface)] mb-2"><?php echo e($title); ?></h3>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
        <p class="text-sm text-[var(--ds-on-surface-variant)] max-w-md mb-6 leading-relaxed">
            <?php echo e($description); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ctaLabel): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ctaAction): ?>
            <button
                type="button"
                wire:click="<?php echo e($ctaAction); ?>"
                class="inline-flex items-center gap-2 px-5 h-11 px-5 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors shadow-soft-md"
            >
                <span class="material-symbols-outlined ms-20"><?php echo e($ctaIcon); ?></span>
                <?php echo e($ctaLabel); ?>

            </button>
        <?php elseif($ctaRoute): ?>
            <a
                href="<?php echo e($ctaRoute); ?>"
                @if (!str_starts_with($ctaRoute, '#') @else wire:navigate <?php endif; ?>
                class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors shadow-soft-md"
            >
                <span class="material-symbols-outlined ms-20"><?php echo e($ctaIcon); ?></span>
                <?php echo e($ctaLabel); ?>

            </a>
        <?php else: ?>
            <?php echo e($cta ?? ''); ?>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php elseif(isset($actions)): ?>
        <div class="flex items-center gap-3">
            <?php echo e($actions); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\feedback\empty-state.blade.php ENDPATH**/ ?>