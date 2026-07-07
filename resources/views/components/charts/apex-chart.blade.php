{{--
/**
 * ApexChart Component - Enterprise Design System
 *
 * Wrapper for ApexCharts
 */
--}}

@props([
    'type' => 'line',
    'series' => [],
    'options' => [],
    'height' => 350,
    'title' => null,
    'strokeCurve' => 'smooth',
])

@php
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
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft" {{ $attributes }}>
    @if ($title)
        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $title }}</h3>
    @endif

    <div x-data="{
        chart: null,
        init() {
            import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                const ApexCharts = module.default;
                const options = {{ json_encode($mergedOptions) }};
                options.series = {{ json_encode($series) }};

                this.chart = new ApexCharts(this.$refs.chart, options);
                this.chart.render();
            });
        }
    }" x-ref="chart"></div>
</div>
