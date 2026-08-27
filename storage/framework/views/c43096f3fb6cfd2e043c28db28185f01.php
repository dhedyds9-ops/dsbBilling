

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'drawer',
    'title' => null,
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
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
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
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
    'sm' => 'w-64 max-w-full',
    'md' => 'w-80 max-w-full',
    'lg' => 'w-96 max-w-full',
    'xl' => 'w-[32rem] max-w-full',
    '2xl' => 'w-[40rem] max-w-full',
    'full' => 'w-full h-full max-w-full max-h-full',
];

$positionClasses = match ($position) {
    'left' => 'inset-y-0 left-0 rounded-none',
    'right' => 'inset-y-0 right-0 rounded-none',
    'top' => 'inset-x-0 top-0 rounded-none',
    'bottom' => 'inset-x-0 bottom-0 rounded-none',
};

$sizeClasses = $sizeMap[$size] ?? $sizeMap['md'];

$translateClasses = match ($position) {
    'left', 'top' => '-translate-x-full',
    'right', 'bottom' => 'translate-x-full',
    default => '',
};
?>

<div
    x-data="{ open: false }"
    x-init="
        $watch('open', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
                $dispatch('<?php echo e($name); ?>-open');
            } else {
                document.body.classList.remove('overflow-hidden');
                $dispatch('<?php echo e($name); ?>-close');
            }
        });
        <?php if($closeOnBackdrop): ?>
            $el.addEventListener('click', (e) => {
                if (e.target === $el) open = false;
            });
        <?php endif; ?>
        <?php if($closeOnEscape): ?>
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && open) open = false;
            });
        <?php endif; ?>
    "
    {{ $name }}.window="open = true"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    <?php echo e($attributes); ?>

>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="<?php echo e($position === 'left' || $position === 'top' ? '-translate-x-full' : 'translate-x-full'); ?>"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="<?php echo e($position === 'left' || $position === 'top' ? '-translate-x-full' : 'translate-x-full'); ?>"
        class="fixed <?php echo e($positionClasses); ?> <?php echo e($sizeClasses); ?> bg-white shadow-soft-xl flex flex-col max-h-full"
        @click.stop
    >
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header) || $title): ?>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                        <h3 class="text-lg font-semibold text-slate-900"><?php echo e($title); ?></h3>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($header ?? ''); ?>

                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                    aria-label="Close drawer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <?php echo e($slot); ?>

        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <?php echo e($footer); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\layout\drawer.blade.php ENDPATH**/ ?>