

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'modal',
    'title' => null,
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'showClose' => true,
    'static' => false,
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
    'name' => 'modal',
    'title' => null,
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'showClose' => true,
    'static' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$sizes = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    'full' => 'max-w-full mx-4',
];

$sizeClasses = $sizes[$size] ?? $sizes['md'];
?>

<div
    x-data="{ show: false, <?php echo e($name); ?>: null }"
    x-init="
        $watch('show', value => {
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
                if (e.target === $el) show = false;
            });
        <?php endif; ?>
        <?php if($closeOnEscape): ?>
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && show) show = false;
            });
        <?php endif; ?>
    "
    {{ $name }}.window="show = true; <?php echo e($name); ?> = $event.detail;"
    {{ $name }}-close.window="show = false"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    <?php echo e($attributes); ?>

>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="<?php echo e($sizeClasses); ?> w-full bg-white dark:bg-slate-800 rounded-2xl shadow-soft-xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col"
        @click.stop
    >
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header) || $showClose || $title): ?>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e($title); ?></h3>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($header ?? ''); ?>

                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClose): ?>
                    <button
                        type="button"
                        @click="show = false"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800 rounded-lg transition-colors"
                        aria-label="Close modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <?php echo e($slot); ?>

        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <?php echo e($footer); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\base\modal.blade.php ENDPATH**/ ?>