

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'left',
    'width' => 'auto',
    'contentClasses' => '',
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
    'align' => 'left',
    'width' => 'auto',
    'contentClasses' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$alignClasses = $align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left';

$widthClasses = $width === 'full' ? 'w-full' : 'w-56';
?>

<div
    x-data="{ open: false }"
    @click.away="open = false"
    @keydown.escape.window="open = false"
    class="relative inline-block"
    <?php echo e($attributes); ?>

>
    
    <div @click="open = !open">
        <?php echo e($trigger ?? $slot); ?>

    </div>

    
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 <?php echo e($alignClasses); ?> <?php echo e($widthClasses); ?> bg-white dark:bg-slate-800 rounded-xl shadow-soft-lg border border-slate-200 dark:border-slate-700 py-2 focus:outline-none"
        style="display: none;"
    >
        <div class="<?php echo e($contentClasses); ?>">
            <?php echo e($content ?? ''); ?>

        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\base\dropdown.blade.php ENDPATH**/ ?>