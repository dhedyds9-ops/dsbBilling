

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'line',
    'series' => [],
    'options' => [],
    'height' => 350,
    'title' => null,
    'strokeCurve' => 'smooth',
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
    'series' => [],
    'options' => [],
    'height' => 350,
    'title' => null,
    'strokeCurve' => 'smooth',
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
    'chart' => [
        'type' => $type,
        'height' => $height,
        'fontFamily' => 'Figtree, sans-serif',
        'toolbar' => [
            'show' => true,
            'tools' => [
                'download' => true,
                'selection' => false,
                'zoom' => true,
                'zoomintreset' => true,
            ],
        ],
        'animations' => [
            'enabled' => true,
            'easing' => 'easeinout',
            'speed' => 800,
        ],
    ],
    'stroke' => [
        'curve' => $strokeCurve,
        'width' => 2,
    ],
    'theme' => [
        'mode' => 'light',
    ],
    'dataLabels' => [
        'enabled' => false,
    ],
    'legend' => [
        'show' => true,
        'position' => 'bottom',
        'horizontalAlign' => 'center',
    ],
    'tooltip' => [
        'theme' => 'dark',
    ],
];

$mergedOptions = array_merge_recursive($defaultOptions, $options);
?>

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
        <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($title); ?></h3>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div x-data="{
        chart: null,
        init() {
            import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                const ApexCharts = module.default;
                const options = <?php echo e(json_encode($mergedOptions)); ?>;
                options.series = <?php echo e(json_encode($series)); ?>;

                this.chart = new ApexCharts(this.$refs.chart, options);
                this.chart.render();
            });
        }
    }" x-ref="chart"></div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\charts\apex-chart.blade.php ENDPATH**/ ?>