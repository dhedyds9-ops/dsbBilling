{{--
/**
 * Stat Card Component - Enterprise Design System
 *
 * Display single key metric with label, value, and trend
 */
--}}

@props([
    'label' => null,
    'value' => 0,
    'prefix' => null,
    'suffix' => null,
    'trend' => null,
    'trendDirection' => null,
    'icon' => null,
    'iconBg' => 'primary',
])

@php
$iconBgColors = [
    'primary' => 'bg-primary-100 text-primary-600',
    'success' => 'bg-success-100 text-success-600',
    'warning' => 'bg-warning-100 text-warning-600',
    'danger' => 'bg-danger-100 text-danger-600',
    'info' => 'bg-info-100 text-info-600',
];

$trendColors = [
    'up' => 'text-success-600',
    'down' => 'text-danger-600',
    'neutral' => 'text-slate-500 dark:text-slate-400',
];

$trendDirection = $trendDirection ?? (
    $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'neutral')
);
@endphp

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-soft" {{ $attributes }}>
    <div class="flex items-start justify-between">
        <div class="space-y-2">
            @if ($label)
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            @endif

            <div class="flex items-baseline gap-1">
                @if ($prefix)
                    <span class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $prefix }}</span>
                @endif

                <span class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}</span>

                @if ($suffix)
                    <span class="text-lg font-medium text-slate-500 dark:text-slate-400">{{ $suffix }}</span>
                @endif
            </div>

            @if ($trend !== null)
                <div class="flex items-center gap-1 {{ $trendColors[$trendDirection] }}">
                    @if ($trendDirection === 'up')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    @elseif ($trendDirection === 'down')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                        </svg>
                    @endif

                    <span class="text-sm font-medium">{{ abs($trend) }}%</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">vs last period</span>
                </div>
            @endif
        </div>

        @if ($icon)
            <div class="p-3 rounded-xl {{ $iconBgColors[$iconBg] }}">
                <x-icon :name="$icon" class="w-6 h-6" />
            </div>
        @endif
    </div>
</div>






