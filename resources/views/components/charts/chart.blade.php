{{--
/**
 * Chart Component - Enterprise Design System
 *
 * Wrapper for Chart.js with consistent styling
 */
--}}

@props([
    'type' => 'line',
    'data' => [],
    'options' => [],
    'height' => 300,
    'title' => null,
])

@php
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
@endphp

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-soft" {{ $attributes }}>
    @if ($title)
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ $title }}</h3>
    @endif

    <div class="relative" style="height: {{ $height }}px;">
        <canvas x-data="{
            chart: null,
            init() {
                import('https://cdn.jsdelivr.net/npm/chart.js').then(module => {
                    const Chart = module.default;
                    const ctx = this.$refs.canvas.getContext('2d');

                    this.chart = new Chart(ctx, {
                        type: '{{ $type }}',
                        data: {{ json_encode($data) }},
                        options: {{ json_encode($mergedOptions) }}
                    });
                });
            }
        }" x-ref="canvas"></canvas>
    </div>
</div>
