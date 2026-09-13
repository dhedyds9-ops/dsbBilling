

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Untitled',
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
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
    'title' => 'Untitled',
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
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
        'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-300',
        'success'   => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
        'warning'   => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
        'danger'    => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
        'info'      => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
    ];
    $iconBgClass = $iconBgMap[$iconBg] ?? $iconBgMap['primary'];
?>

<section class="relative overflow-hidden">
    <div
        class="relative
               bg-ds-surface
               border border-slate-200 dark:border-slate-700 rounded-2xl shadow-soft-sm"
    >
        
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 opacity-[0.07]
                   bg-[radial-gradient(circle_at_top_right,var(--ds-primary),transparent_55%),
                      radial-gradient(circle_at_bottom_left,var(--ds-secondary),transparent_55%)]"
        ></div>

        <div class="relative p-5 sm:p-6 flex flex-col gap-5">
            <div
                class="flex flex-col md:flex-row md:items-start md:justify-between
                       gap-5"
            >
                
                <div class="flex items-start gap-4 min-w-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
                        <div
                            class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center
                                   <?php echo e($iconBgClass); ?> shadow-soft-sm"
                        >
                            <span class="material-symbols-outlined fill ms-28"><?php echo e($icon); ?></span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="min-w-0 flex-1 space-y-2">
                        <h1
                            class="text-xl sm:text-2xl font-extrabold tracking-tight
                                   text-ds-on-surface leading-[1.2]"
                        >
                            <?php echo e($title); ?>

                        </h1>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle || isset($metadata)): ?>
                            <div class="space-y-1.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                                    <p class="text-sm text-ds-on-surface-variant leading-relaxed">
                                        <?php echo e($subtitle); ?>

                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($metadata)): ?>
                                    <div><?php echo e($metadata); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actions)): ?>
                    <div
                        class="flex items-center gap-2 flex-wrap
                               md:justify-end md:shrink-0"
                    >
                        <?php echo e($actions); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($toolbar)): ?>
                <div
                    class="pt-4 mt-2 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center gap-3"
                >
                    <?php echo e($toolbar); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>






<?php /**PATH D:\dsBilling\resources\views\components\admin\page-header.blade.php ENDPATH**/ ?>