<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'drawer',
    'title' => null,
    'subtitle' => null,
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'persistBody' => false,
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
    'name' => 'drawer',
    'title' => null,
    'subtitle' => null,
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'persistBody' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$sizeMap = [
    'sm'  => 'w-64 max-w-full',
    'md'  => 'w-80 max-w-full',
    'lg'  => 'w-96 max-w-full',
    'xl'  => 'w-[32rem] max-w-full',
    '2xl' => 'w-[40rem] max-w-full',
    'full' => 'w-full h-full max-w-full max-h-full',
];

$positionAxis = match ($position) {
    'left', 'right' => 'x',
    'top', 'bottom'  => 'y',
    default => 'x',
};

$positionClasses = match ($position) {
    'left'   => 'inset-y-0 left-0 rounded-none rounded-r-3xl',
    'right'  => 'inset-y-0 right-0 rounded-none rounded-l-3xl',
    'top'    => 'inset-x-0 top-0 rounded-none rounded-b-3xl h-auto max-h-[80vh]',
    'bottom' => 'inset-x-0 bottom-0 rounded-none rounded-t-3xl h-auto max-h-[85vh]',
    default => 'inset-y-0 right-0 rounded-none rounded-l-3xl',
};

$translateStart = match ($position) {
    'left'   => '-translate-x-full',
    'right'  => 'translate-x-full',
    'top'    => '-translate-y-full',
    'bottom' => 'translate-y-full',
    default => 'translate-x-full',
};

$sizeClasses = $sizeMap[$size] ?? $sizeMap['md'];
?>

<div
    x-data="{ open: false }"
    x-init="
        const syncBodyLock = (val) => {
            if (val) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        };
        $watch('open', value => {
            syncBodyLock(value);
            $dispatch(value ? '<?php echo e($name); ?>-open' : '<?php echo e($name); ?>-close');
        });
        <?php if($closeOnBackdrop): ?>
        $el.addEventListener('mousedown', (e) => { if (e.target === $el) open = false; });
        <?php endif; ?>
        <?php if($closeOnEscape): ?>
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && open) open = false; });
        <?php endif; ?>
        syncBodyLock(open);
    "
    {{ $name }}.window="open = true"
    {{ $name }}-close.window="open = false"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[70] bg-slate-950/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-label="<?php echo e($name); ?>"
    <?php echo e($attributes); ?>

>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="<?php echo e($translateStart); ?> opacity-60"
        x-transition:enter-end="translate-x-0 translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 translate-y-0 opacity-100"
        x-transition:leave-end="<?php echo e($translateStart); ?> opacity-0"
        class="fixed <?php echo e($positionClasses); ?> <?php echo e($sizeClasses); ?> bg-[var(--ds-surface)] border-[var(--ds-outline-variant)] <?php echo e(in_array($position, ['left','right']) ? 'border-l' : 'border-t'); ?> shadow-soft-2xl flex flex-col max-h-full"
        @click.stop
        <?php if($persistBody): ?> x-ignore <?php endif; ?>
    >
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header) || $title): ?>
            <div class="flex items-start justify-between gap-3 px-6 py-4 border-b border-[var(--ds-outline-variant)]">
                <div class="flex-1 min-w-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                        <?php echo e($header); ?>

                    <?php else: ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                            <h3 class="text-base font-semibold text-[var(--ds-on-surface)] leading-tight"><?php echo e($title); ?></h3>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                            <p class="text-xs text-[var(--ds-on-surface-variant)] mt-1 leading-relaxed"><?php echo e($subtitle); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="flex-shrink-0 h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                    aria-label="Tutup panel"
                >
                    <span class="material-symbols-outlined ms-22">close</span>
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="flex-1 overflow-y-auto overflow-x-hidden ds-scrollbar">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($headerExtra)): ?>
                <div class="px-6 pt-4"><?php echo e($headerExtra); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="px-6 py-5">
                <?php echo e($slot); ?>

            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
            <div class="px-6 py-4 border-t border-[var(--ds-outline-variant)] bg-[var(--ds-surface-container-low)] flex flex-wrap items-center justify-end gap-2">
                <?php echo e($footer); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\ui\drawer.blade.php ENDPATH**/ ?>