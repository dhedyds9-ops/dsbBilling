

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'line',
    'data' => [],
    'options' => [],
    'height' => 300,
    'title' => null,
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
    'type' => 'line',
    'data' => [],
    'options' => [],
    'height' => 300,
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$defaultOptions = [
    'responsive' => true,
    'maintainAspectRatio' => false,
    'plugins' => [
        'legend' => [
            'display' => true,
            'position' => 'bottom',
            'labels' => [
                'usePointStyle' => true,
                'padding' => 20,
                'font' => [
                    'family' => 'Figtree, sans-serif',
                ],
            ],
        ],
        'tooltip' => [
            'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
            'titleFont' => ['family' => 'Figtree, sans-serif'],
            'bodyFont' => ['family' => 'Figtree, sans-serif'],
            'padding' => 12,
            'cornerRadius' => 8,
        ],
    ],
    'scales' => [
        'x' => [
            'grid' => [
                'display' => false,
            ],
            'ticks' => [
                'font' => [
                    'family' => 'Figtree, sans-serif',
                ],
            ],
        ],
        'y' => [
            'grid' => [
                'color' => 'rgba(148, 163, 184, 0.1)',
            ],
            'ticks' => [
                'font' => [
                    'family' => 'Figtree, sans-serif',
                ],
            ],
        ],
    ],
];

$mergedOptions = array_merge_recursive($defaultOptions, $options);
?>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-soft" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4"><?php echo e($title); ?></h3>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="relative" style="height: <?php echo e($height); ?>px;">
        <canvas x-data="{
            chart: null,
            init() {
                import('https://cdn.jsdelivr.net/npm/chart.js').then(module => {
                    const Chart = module.default;
                    const ctx = this.$refs.canvas.getContext('2d');

                    this.chart = new Chart(ctx, {
                        type: '<?php echo e($type); ?>',
                        data: <?php echo e(json_encode($data)); ?>,
                        options: <?php echo e(json_encode($mergedOptions)); ?>

                    });
                });
            }
        }" x-ref="canvas"></canvas>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\components\charts\chart.blade.php ENDPATH**/ ?>